<?php
/*
print_equiposByJornada.php

Copyright  2013-2016 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

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
 * genera un pdf ordenado con los participantes en jornada de prueba por equipos
*/

require_once(__DIR__."/fpdf.php");
require_once(__DIR__."/../tools.php");
require_once(__DIR__."/../logging.php");
require_once(__DIR__.'/../database/classes/DBObject.php');
require_once(__DIR__.'/../database/classes/Pruebas.php');
require_once(__DIR__.'/../database/classes/Jornadas.php');
require_once(__DIR__.'/../database/classes/Equipos.php');
require_once(__DIR__."/print_common.php");

class OrdenSalidaEquipos4 extends PrintCommon {
	protected $equipos; // lista de equipos de esta jornada
    protected $perros; // lista de participantes en esta jornada
    protected $manga; // datos de la manga
    protected $categoria;
	
	// geometria de las celdas
	protected $cellHeader;
    //                      Dorsal  nombre raza licencia Categoria guia club  celo  observaciones
	protected $pos	=array( 10,     25,     27,    10,    18,      40,   25,  10,    25);
	protected $align=array( 'R',    'C',    'R',    'C',  'C',     'R',  'R', 'C',   'R');
	
	/**
	 * Constructor
     * @param {integer} $prueba Prueba ID
     * @param {integer} $jornada Jormada ID
     * @param {integer} $manga Manga ID
	 * @throws Exception
	 */
	function __construct($prueba,$jornada,$manga) {
		parent::__construct('Portrait',"print_entradaDeDatosEquipos4",$prueba,$jornada);
		if ( ($prueba<=0) || ($jornada<=0) ) {
			$this->errormsg="print_datosEquipos4: either prueba or jornada data are invalid";
			throw new Exception($this->errormsg);
		}
        // comprobamos que estamos en una jornada por equipos
        $flag=intval($this->jornada->Equipos3)+intval($this->jornada->Equipos4);
        if ($flag==0) {
            $this->errormsg="print_datosEquipos4: Jornada $jornada has no Team competition declared";
            throw new Exception($this->errormsg);
        }
        // guardamos info de la manga
        $this->manga=$this->myDBObject->__getObject("Mangas",$manga);
        // Datos del orden de salida de equipos
        $m = new OrdenSalida("ordenSalidaEquipos4",$manga);
        $teams= $m->getTeams();
        $this->equipos=$teams['rows'];
        // anyadimos el array de perros del equipo
        foreach($this->equipos as &$equipo) {$equipo['Perros']=array();}
        $r= $this->myDBObject->__select("*","Resultados","(Manga=$manga)","","");
        foreach($r['rows'] as $perro) {
            foreach($this->equipos as &$equipo) {
                if ($perro['Equipo']==$equipo['ID']) {
                    array_push($equipo['Perros'],$perro);
                    break;
                }
            }
        }
	}
	
	// Cabecera de página
	function Header() {
		$this->print_commonHeader(_("Starting order")." ("._("Teams").")");

        // pintamos datos de la jornada
        $this->SetFont($this->getFontName(),'B',12); // bold 15
        $str  = $this->jornada->Nombre . " - " . $this->jornada->Fecha;
        $this->Cell(90,9,$str,0,0,'L',false);

        // pintamos tipo y categoria de la manga
        $tmanga= Mangas::$tipo_manga[$this->manga->Tipo][1];
        $categoria=$this->getCatString($this->categoria);
        $str2 = "$tmanga - $categoria";
        $this->Cell(100,9,$str2,0,0,'R',false); // al otro lado tipo y categoria de la manga
        $this->Ln(12);
	}
	
	// Pie de página
	function Footer() {
		$this->print_commonFooter();
	}
	
	function printTeamInfo($rowcount,$index,$team,$members) {
        // evaluate logos
        $logos=array('null.png','null.png','null.png','null.png');
        if ($team['Nombre']==="-- Sin asignar --") {
            $logos[0]=getIconPath($this->federation->get('Name'),"agilitycontest.png");
        } else {
            $count=0;
            foreach($members as $miembro) {
                $logo=$this->getLogoName($miembro['Perro']);
                if ( ( ! in_array($logo,$logos) ) && ($count<4) ) $logos[$count++]=$logo;
            }
        }
        // posicion de la celda
        $y=45+22*($rowcount);
        $this->SetXY(10,$y);
        // caja de datos de perros
        $this->ac_header(1,16);
        $this->Cell(12,18,1+$index,'LTBR',0,'C',true);
        $this->ac_header(2,16);
        $this->Cell(103,18,"","TB",0,'C',true);
        $this->SetY($y+1);
        foreach($members as $id => $perro) {
            $this->SetX(23);
            $this->ac_row($id,8);
            $this->Cell(6,4,$perro['Dorsal'],'LTBR',0,'L',true);
            $this->SetFont($this->getFontName(),'B',8);
            $this->Cell(13,4,$perro['Nombre'],'LTBR',0,'C',true);
            $this->SetFont($this->getFontName(),'',7);
            $this->Cell(20,4,$perro['Raza'],'LTBR',0,'R',true);
            $this->SetFont($this->getFontName(),'',7);
            $this->Cell(15,4,$this->getCatString($perro['Categoria']),'LTBR',0,'C',true);
            $this->SetFont($this->getFontName(),'',7);
            $this->Cell(27,4,$perro['NombreGuia'],'LTBR',0,'R',true);
            $this->SetFont($this->getFontName(),'',7);
            $this->Cell(20,4,$perro['NombreClub'],'LTBR',0,'R',true);
            $this->Ln(4);
        }
        // caja de datos del equipo
        $this->SetXY(125,$y);
        $this->ac_header(2,14);
        $this->Cell(65,7,$team['Nombre'],'TB',0,'R',true);
        $this->Cell(10,7,'','TRB',0,'',true); // empty space at right of page

        $this->SetXY(125,7+$y);
        $this->Image($logos[0],$this->getX(),$this->getY(),11);
        $this->Image($logos[1],$this->getX()+11,$this->getY(),11);
        $this->Image($logos[2],$this->getX()+22,$this->getY(),11);
        $this->Image($logos[3],$this->getX()+33,$this->getY(),11);

        $this->Ln(11);
	}
	
	// Tabla coloreada
	function composeTable() {
		$this->myLogger->enter();
        $index=0;
        $rowcount=0;
        $this->categoria="-";
		foreach($this->equipos as $equipo) {
            $miembros=$equipo['Perros'];
            $num=count($miembros);
            if ($num==0) continue; // skip empty teams
            if ( ($rowcount%11==0) || ($equipo['Categorias']!=$this->categoria)) {
                $rowcount=0;
                if($this->categoria!=$equipo['Categorias']) $index=0;
                $this->categoria=$equipo['Categorias'];
                $this->AddPage();
            }
            // pintamos el aspecto general de la celda
            $this->printTeamInfo($rowcount,$index,$equipo,$miembros);
            $rowcount++;
            $index++;
		}
		// Línea de cierre
		$this->myLogger->leave();
	}
}

// Consultamos la base de datos
try {
	$prueba=http_request("Prueba","i",0);
	$jornada=http_request("Jornada","i",0);
    $manga=http_request("Manga","i",0);
    // 	Creamos generador de documento
    $pdf=new OrdenSalidaEquipos4($prueba,$jornada,$manga);
	$pdf->AliasNbPages();
	$pdf->composeTable();
	$pdf->Output("ordenSalidaEquipos4.pdf","D"); // "D" means open download dialog
} catch (Exception $e) {
	die ("Error accessing database: ".$e->getMessage());
};
?>