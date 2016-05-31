<?php
/*
print_resultadosByManga.php

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
 * genera un pdf con los participantes ordenados segun los resultados de la manga
 */

require_once(__DIR__."/fpdf.php");
require_once(__DIR__."/../tools.php");
require_once (__DIR__."/../logging.php");
require_once(__DIR__.'/../database/classes/DBObject.php');
require_once(__DIR__.'/../database/classes/Pruebas.php');
require_once(__DIR__.'/../database/classes/Jueces.php');
require_once(__DIR__.'/../database/classes/Jornadas.php');
require_once(__DIR__.'/../database/classes/Mangas.php');
require_once(__DIR__.'/../database/classes/Resultados.php');
require_once(__DIR__.'/../database/classes/Equipos.php');
require_once(__DIR__."/print_common.php");

class ResultadosByEquipos3 extends PrintCommon {
	
	protected $manga;
	protected $resultados;
    protected $equipos;
	protected $mode;
    protected $eqmgr;
    protected $defaultPerro = array( // participante por defecto para garantizar que haya 4perros/equipo
        'Dorsal' => '-',
        'Perro' => 0,
        'Nombre' => '-',
        'NombreGuia' => '-',
        'NombreClub' => '-',
        'Licencia' => '-',
        'Categoria' => '-',
        'Faltas' => 0,
        'Tocados' => 0,
        'Rehuses' => 0,
        'Tiempo' => '-',
        'Velocidad' => '-',
        'Penalizacion' => 400,
        'Calificacion' => '-',
        'CShort' => '-',
        'Puesto' => '-'
    );
	// geometria de las celdas
	protected $cellHeader;
                    //     Dors    Nombre  Lic     Guia   Club    Cat     Flt    Toc    Reh     Tiempo   vel   penal calif   puesto, equipo
	protected $pos	=array(  7,		18,		15,		30,		24,	    7,	   5,      5,    5,       11,     7,    12,    10,	 7,  25);
	protected $align=array(  'L',   'L',    'C',    'R',   'R',    'C',    'C',   'C',   'C',     'R',    'R',  'R',   'C',	 'C', 'R');
	
	/**
	 * Constructor
	 * @param {obj} $manga datos de la manga
	 * @param {obj} $resultados resultados asociados a la manga/categoria pedidas
	 * @throws Exception
	 */
	function __construct($prueba,$jornada,$manga,$resultados,$mode) {
		parent::__construct('Portrait',"print_resultadosEquipos3",$prueba,$jornada);
		$this->manga=$manga;
		$this->resultados=$resultados;
        $this->mode=$mode;
        $mindogs=0;
        switch(intval($this->jornada->Equipos3)) {
            case 1:$mindogs=3; break; // old style 3 best of 4
            case 2:$mindogs=2; break; // 2 besto of 3
            case 3:$mindogs=3; break; // 3 best of 4
            default: break;
        }
        switch(intval($this->jornada->Equipos4)) {
            case 1:$mindogs=4; break; // old style 4 combined
            case 2:$mindogs=2; break; // 2 combined
            case 3:$mindogs=3; break; // 3 combined
            case 4:$mindogs=4; break; // 4 combined
            default: break;
        }
        $this->cellHeader=
            array(_('Dorsal'),_('Name'),_('Lic').'.',_('Handler'),$this->strClub,_('Cat').'.',_('Flt').'.',_('Tch').'.',_('Ref').'.',
                  _('Time'),_('Vel').'.',_('Penal').'.',_('Calification'),_('Position'),_('Team global'));
        $this->equipos=Resultados::getTeamResults($resultados['rows'],$prueba,$jornada,$mindogs);
        $this->eqmgr=new Equipos("print_resultadosByEquipos",$prueba,$jornada);
	}
	
	// Cabecera de página
	function Header() {
		$this->print_commonHeader(_("Round scores")." ("._("Teams").")");
		$this->print_identificacionManga($this->manga,$this->getModeString(intval($this->mode)));
		
		// Si es la primera hoja pintamos datos tecnicos de la manga
		if ($this->PageNo()!=1) return;

		$this->SetFont($this->getFontName(),'B',9); // bold 9px
		$jobj=new Jueces("print_resultadosEquipos3");
		$juez1=$jobj->selectByID($this->manga->Juez1);
		$juez2=$jobj->selectByID($this->manga->Juez2);
		$this->Cell(20,5,_("Judge")." 1:","LT",0,'L',false);
		$str=($juez1['Nombre']==="-- Sin asignar --")?"":$juez1['Nombre'];
		$this->Cell(70,5,$str,"T",0,'L',false);
		$this->Cell(20,5,_("Judge")." 2:","T",0,'L',false);
		$str=($juez2['Nombre']==="-- Sin asignar --")?"":$juez2['Nombre'];
		$this->Cell(78,5,$str,"TR",0,'L',false);
		$this->Ln(5);
		$this->Cell(20,5,_("Distance").":","LB",0,'L',false);
		$this->Cell(25,5,"{$this->resultados['trs']['dist']} mts","B",0,'L',false);
		$this->Cell(20,5,_("Obstacles").":","B",0,'L',false);
		$this->Cell(25,5,$this->resultados['trs']['obst'],"B",0,'L',false);
		$this->Cell(10,5,_("SCT").":","B",0,'L',false);
		$this->Cell(20,5,"{$this->resultados['trs']['trs']} seg.","B",0,'L',false);
		$this->Cell(10,5,_("MCT").":","B",0,'L',false);
		$this->Cell(20,5,"{$this->resultados['trs']['trm']} seg.","B",0,'L',false);
		$this->Cell(20,5,_("Speed").":","B",0,'L',false);
		$this->Cell(18,5,"{$this->resultados['trs']['vel']} m/s","BR",0,'L',false);
		$this->Ln(5);
	}
	
	// Pie de página
	function Footer() {
		$this->print_commonFooter();
	}

    function printTeamInformation($teamcount,$team) {
        // evaluate logos
        $logos=array('null.png','null.png','null.png','null.png');
        if ($team['Nombre']==="-- Sin asignar --") {
            $logos[0]=getIconPath($this->federation->get('Name'),"agilitycontest.png");
        } else {
            $miembros=$this->eqmgr->getPerrosByTeam($team['ID']);
            $count=0;
            for ($n=0;$n<count($miembros);$n++) {
                $logo=getIconPath($this->federation->get('Name'),$miembros[$n]['LogoClub']);
                if ( ( ! in_array($logo,$logos) ) && ($count<4) ) $logos[$count++]=$logo;
            }
        }
        $offset=($this->PageNo()==1)?57:45;
        $this->SetXY(10,$offset+38*($teamcount%6));
        $this->ac_header(1,18);
        $this->Cell(15,10,strval(1+$teamcount)." -",'LT',0,'C',true); // imprime puesto del equipo
        $x=$this->getX();
        $y=$this->getY();
        for ($n=0;$n<4;$n++) {
            if ($logos[$n]==="null.png") {
                $this->SetX($x+10*$n);
                $this->Cell(10,10,"",'T',0,'C',true);
            } else {
                $this->Image($logos[$n],$x+10*$n,$y,10);
            }
        }
        $this->SetX($x+40);
        $this->Cell(125,10,$team['Nombre'],'T',0,'R',true);
        $this->Cell(8,10,'','TR',0,'R',true); // empty space at right of page
        $this->Ln();
        $this->ac_header(2,8);
        for($i=0;$i<count($this->cellHeader);$i++) {
            // en la cabecera texto siempre centrado. Si caza skip licencia
            if ($this->pos[$i]!=0) $this->Cell($this->pos[$i],5,$this->cellHeader[$i],1,0,'C',true);
        }
        $this->ac_row(2,9);
        $this->Ln();
    }

	// Tabla coloreada
	function composeTable() {
		$this->myLogger->enter();
		if ($this->federation->get('WideLicense')) {
            // en la cabecera texto siempre centrado. Si caza skip licencia
            $this->pos[1]+=5; $this->pos[2]=0; $this->pos[3]+=5;$this->pos[4]+=5;
        }
		$this->ac_SetDrawColor($this->config->getEnv('pdf_linecolor'));
		$this->SetLineWidth(.3);
		
		// Datos
		$teamcount=0;
        foreach($this->equipos as $equipo) {
            // REMINDER: $this->cell( width, height, data, borders, where, align, fill)
            // si el equipo no tiene participantes es que la categoria no es válida: skip
            if (count($equipo['Resultados'])==0) continue;
            if( ( $teamcount % 6) == 0 ) { // assume 40mmts/team)
                $this->addPage();
            }
            // evaluate puesto del equipo
            // $this->myLogger->trace("imprimiendo datos del equipo {$equipo['ID']} - {$equipo['Nombre']}");
            $this->printTeamInformation($teamcount,$equipo);
            // print team header/data
            for ($n=0;$n<4;$n++) {
                $this->ac_row($n,9);
                // con independencia de los perros del equipo imprimiremos siempre 4 columnas
                $row=$this->defaultPerro;
                if (array_key_exists($n,$equipo['Resultados'])) $row=$equipo['Resultados'][$n];
                // print team member's result
                // $this->myLogger->trace("imprimiendo datos del perro {$row['Perro']} - {$row['Nombre']}");
                // properly format special fields
                $puesto= ($row['Penalizacion']>=200)? "-":"{$row['Puesto']}º";
                $veloc= ($row['Penalizacion']>=200)?"-":number_format($row['Velocidad'],1);
                $tiempo= ($row['Penalizacion']>=200)?"-":number_format($row['Tiempo'],$this->timeResolution);
                $penal=number_format($row['Penalizacion'],$this->timeResolution);

                // print row data
                $this->SetFont($this->getFontName(),'',8); // set data font size
                $this->Cell($this->pos[0],5,$row['Dorsal'],			'LBR',	0,		$this->align[0],	true);
                $this->SetFont($this->getFontName(),'B',8); // mark Nombre as bold
                $this->Cell($this->pos[1],5,$row['Nombre'],			'LBR',	0,		$this->align[1],	true);
                $this->SetFont($this->getFontName(),'',8); // set data font size
                if ($this->pos[2]!=0) $this->Cell($this->pos[2],5,$row['Licencia'],		'LBR',	0,		$this->align[2],	true);
                $this->Cell($this->pos[3],5,$row['NombreGuia'],		'LBR',	0,		$this->align[3],	true);
                $this->Cell($this->pos[4],5,$row['NombreClub'],		'LBR',	0,		$this->align[4],	true);
                // en pruebas por equipos el grado se ignora
                // $this->Cell($this->pos[5],6,$row['Categoria'].' - '.$row['Grado'],	'LR',	0,		$this->align[5],	$fill);
                $this->Cell($this->pos[5],5,$row['Categoria'],  	'LBR',	0,		$this->align[5],	true);
                $this->Cell($this->pos[6],5,$row['Faltas'],			'LBR',	0,		$this->align[6],	true);
                $this->Cell($this->pos[7],5,$row['Tocados'],		'LBR',	0,		$this->align[7],	true);
                $this->Cell($this->pos[8],5,$row['Rehuses'],		'LBR',	0,		$this->align[8],	true);
                $this->Cell($this->pos[9],5,$tiempo,				'LBR',	0,		$this->align[9],	true);
                $this->Cell($this->pos[10],5,$veloc,				'LBR',	0,		$this->align[10],	true);
                $this->Cell($this->pos[11],5,$penal,				'LBR',	0,		$this->align[11],	true);
                $this->Cell($this->pos[12],5,$row['CShort'],	'LBR',	0,		$this->align[12],	true);
                $this->Cell($this->pos[13],5,$puesto,			'LBR',	0,		$this->align[13],	true);
                $this->ac_header(2,8);
                // en las dos primeras filas imprimimos informacion de resultados del equipo
                if ($n==0) {
                    $tg=number_format($equipo['Tiempo'],$this->timeResolution);
                    $this->Cell($this->pos[14],5,_("Time").": $tg",	'LBR',	0,		$this->align[14],	true);
                }
                if ($n==1) {
                    $pg=number_format($equipo['Penalizacion'],$this->timeResolution);
                    $this->Cell($this->pos[14],5,_("Penaliz").".: $pg",	'LBR',	0,		$this->align[14],	true);
                }
                $this->ac_row(2,9);
                $this->Ln(5);
            }
            $teamcount++;
        }
        $this->myLogger->leave();
	}
}

// Consultamos la base de datos
try {
	$idprueba=http_request("Prueba","i",0);
	$idjornada=http_request("Jornada","i",0);
	$idmanga=http_request("Manga","i",0);
	$mode=http_request("Mode","i",0);
	
	$mngobj= new Mangas("printResultadosByManga",$idjornada);
	$manga=$mngobj->selectByID($idmanga);
	$resobj= new Resultados("printResultadosByManga",$idprueba,$idmanga);
	$resultados=$resobj->getResultados($mode); // throw exception if pending dogs

	// Creamos generador de documento
	$pdf = new ResultadosByEquipos3($idprueba,$idjornada,$manga,$resultados,$mode);
	$pdf->AliasNbPages();
	$pdf->composeTable();
	$pdf->Output("resultadosEquipos3.pdf","D"); // "D" means open download dialog
} catch (Exception $e) {
    die ("Error accessing database: ".$e->getMessage());
}
?>