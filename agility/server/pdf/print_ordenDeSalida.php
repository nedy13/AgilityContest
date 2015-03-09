<?php
/*
print_ordenDeSalida.php

Copyright 2013-2015 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

This program is free software; you can redistribute it and/or modify it under the terms 
of the GNU General Public License as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/


header('Set-Cookie: fileDownload=true; path=/');
// mandatory 'header' to be the first element to be echoed to stdout

/**
 * genera un pdf ordenado por club, categoria y nombre con una pagina por cada jornada
*/

require_once(__DIR__."/fpdf.php");
require_once(__DIR__."/../tools.php");
require_once(__DIR__."/../logging.php");
require_once(__DIR__.'/../database/classes/DBObject.php');
require_once(__DIR__.'/../database/classes/Pruebas.php');
require_once(__DIR__.'/../database/classes/Jornadas.php');
require_once(__DIR__.'/../database/classes/Mangas.php');
require_once(__DIR__.'/../database/classes/OrdenSalida.php');
require_once(__DIR__."/print_common.php");

class PDF extends PrintCommon {

	protected $manga; // datos de la manga
	protected $orden; // orden de salida
	protected $categoria; // categoria que estamos listando
	protected $teams; // lista de equipos de esta jornada
	
	// geometria de las celdas
	protected $cellHeader;
					//		Orden    Nombre Raza	Dorsal Lic	Guia	Club	Celo	Observaciones 
 	protected $pos	=array(  12,      30,	25,     12,     12,    40,   28,     10,    20);
	protected $align=array(  'R',    'C',	'R',    'R',    'C',   'R',  'R',    'C',   'R');
	protected $cat  =array("-" => "","L"=>"Large","M"=>"Medium","S"=>"Small","T"=>"Tiny");
	
	/**
	 * Constructor
	 * @param {integer} $prueba Prueba ID
	 * @param {array} $inscritos Lista de inscritos en formato jquery array[count,rows[]]
	 * @throws Exception
	 */
	function __construct($prueba,$jornada,$manga) {
		parent::__construct('Portrait',$prueba,$jornada);
		if ( ($prueba<=0) || ($jornada<=0) || ($manga<=0) ) {
			$this->errormsg="printOrdenDeSalida: either prueba/jornada/ manga/orden data are invalid";
			throw new Exception($this->errormsg);
		}
		// Datos de la manga
		$m = new Mangas("printOrdenDeSalida",$jornada);
		$this->manga= $m->selectByID($manga);
		// Datos del orden de salida
		$o = new OrdenSalida("printOrdenDeSalida",$manga);
		$os= $o->getData();
		$this->orden=$os['rows'];
		$this->categoria="L";
		$this->cellHeader=
			array(_('Orden'),_('Nombre'),_('Raza'),_('Dorsal'),_('Lic.'),_('Guía'),_('Club'),_('Celo'),_('Observaciones'));
		$eq=new Equipos("print_ordenDeSalida",$prueba,$jornada);
		$this->teams=$eq->getTeamsByJourney();
	}
	
	private function isTeam() {
		switch ($this->manga->Tipo) {
			case 8: case 9: case 13: case 14: return true;
			default: return false;
		}
	}
	
	// Cabecera de página
	function Header() {
		$this->print_commonHeader(_("Orden de Salida"));
		$this->print_identificacionManga($this->manga,$this->cat[$this->categoria]);
	}
	
	// Pie de página
	function Footer() {
		$this->print_commonFooter();
	}
	
	function writeTableHeader() {
		// Colores, ancho de línea y fuente en negrita de la cabecera de tabla
		$this->ac_header(1,8);
		for($i=0;$i<count($this->cellHeader);$i++) {
			// en la cabecera texto siempre centrado
			$this->Cell($this->pos[$i],7,$this->cellHeader[$i],1,0,'C',true);
		}
		$this->Ln();
	}
	
	function printTeamInformation($team) {
		$this->ac_header(2,8);
		$nombre=$this->teams[$team]['Nombre'];
		$this->Cell(185,6,_("Equipo").": $nombre",'LTBR',0,'L',true);
		$this->Ln();
	}
	
	// Tabla coloreada
	function composeTable() {
		$this->myLogger->enter();
		
		$this->ac_SetDrawColor($this->config->getEnv('pdf_linecolor'));
		$this->SetLineWidth(.3);
		
		// Datos
		$rowcount=0;
		$order=0;
		$lastTeam=0;
		foreach($this->orden as $row) {
			$newTeam=intval($row['Equipo']);
			// REMINDER: $this->cell( width, height, data, borders, where, align, fill)
			// if change in categoria, reset orden counter and force page change
			if ($row['Categoria'] !== $this->categoria) {
				$this->categoria = $row['Categoria'];
				$this->Cell(array_sum($this->pos),0,'','T'); // forzamos linea de cierre
				$rowcount=0;
				$order=0;
				$lastTeam=0;
			}
			if ($this->isTeam()) {
				// team change: make sure that new team fits in page
				if ($newTeam!=$lastTeam) {
					if ($rowcount>=32) $rowcount=37; // team change: seek at end of page
				}
			}
			if ( ($rowcount==0) || ($rowcount>=37) ) { // assume 38 rows per page ( rowWidth = 6mmts )
				if ($rowcount>0)
					$this->Cell(array_sum($this->pos),0,'','T'); // linea de cierre en cambio de pagina
				$rowcount=0;
				$this->addPage();
				$this->writeTableHeader();
				$lastTeam=0; // force writting of team header information
			}
			// on team Events and team change add Team header information
			if ( $this->isTeam() && ($newTeam!=$lastTeam) ) {
				$lastTeam=$newTeam;
				$this->printTeamInformation($lastTeam);
				$rowcount++;
			}
			$this->ac_row($order%2,8);
			$this->SetFont('Arial','B',10); // bold 9px
			$this->Cell($this->pos[0],6,($order+1)." - ",'LR',0,$this->align[0],true); // display order
			$this->SetFont('Arial','B',9); // remove bold 9px
			$this->Cell($this->pos[1],6,$row['Nombre'],		'LR',0,$this->align[1],true);
			$this->SetFont('Arial','',8); // remove bold 9px
			$this->Cell($this->pos[2],6,$row['Raza'],		'LR',0,$this->align[2],true);
			$this->Cell($this->pos[3],6,$row['Dorsal'],		'LR',0,$this->align[3],true);
			$this->Cell($this->pos[4],6,$row['Licencia'],	'LR',0,$this->align[4],true);
			$this->Cell($this->pos[5],6,$row['NombreGuia'],	'LR',0,$this->align[5],true);
			$this->Cell($this->pos[6],6,$row['NombreClub'],	'LR',0,$this->align[6],true);
			$this->Cell($this->pos[7],6,($row['Celo']==0)?"":_("Celo"),		'LR',0,$this->align[7],true);
			$this->Cell($this->pos[8],6,$row['Observaciones'],'LR',0,$this->align[8],true);
			$this->Ln();
			$rowcount++;
			$order++;
		}
		// Línea de cierre
		$this->Cell(array_sum($this->pos),0,'','T');
		$this->myLogger->leave();
	}
}

// Consultamos la base de datos
try {
	$prueba=http_request("Prueba","i",0);
	$jornada=http_request("Jornada","i",0);
	$manga=http_request("Manga","i",0);
	// 	Creamos generador de documento
	$pdf = new PDF($prueba,$jornada,$manga);
	$pdf->AliasNbPages();
	$pdf->composeTable();
	$pdf->Output("ordenDeSalida.pdf","D"); // "D" means open download dialog	
} catch (Exception $e) {
	die ("Error accessing database: ".$e.getMessage());
};
?>;