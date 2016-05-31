<?php
class RSCE extends Federations {

    function __construct() {
        $this->config= array (
            'ID'    => 0,
            'Name'  => 'RSCE',
            'LongName' => 'Real Sociedad Canina de España',
            // use basename http absolute path for icons, as need to be used in client side
            'OrganizerLogo'     => '/agility/modules/rsce/rsce.png',
            'Logo'     => '/agility/modules/rsce/rsce.png',
            'ParentLogo'   => '/agility/modules/rsce/fci.png',
            'WebURL' => 'http://www.rsce.es/',
            'ParentWebURL' => 'http://www.fci.org/',
            'Heights' => 3,
            'Grades' => 3,
            'International' => 0,
            'WideLicense' => false, // some federations need extra print space to show license ID
            'Recorridos' => array('Common course',"Standard / Midi + Mini","Separate courses"),
            'ListaGradosShort' => array(
                '-' => 'Sin especificar',
                'Baja' => 'Out',
                'GI' => 'GI',
                'GII'=> 'GII',
                'GIII' => 'GIII',
                'P.A.' => 'P.A.',
                'P.B.' => 'P.B.', // "perro en blanco"
                'Ret.' => 'Ret.'
            ),
            'ListaGrados'    => array (
                '-' => 'Sin especificar',
                'GI' => 'Grado I',
                'GII'=> 'Grado II',
                'GIII' => 'Grado III',
                'P.A.' => 'Pre-Agility',
                'P.B.' => 'Perro en Blanco',
                'Baja' => 'Baja temporal',
                'Ret.' => 'Retirado',
            ),
            'ListaCategoriasShort' => array (
                '-' => '-',
                'L' => 'Std',
                'M' => 'Midi',
                'S' => 'Mini',
                // 'T' => 'Toy' // no existe
            ),
            'ListaCategorias' => array (
                '-' => 'Sin especificar',
                'L' => 'Standard',
                'M' => 'Midi',
                'S' => 'Mini',
                // 'T' => 'Toy' // no existe
            ),
            'InfoManga' => array(
                array('L' => _('Standard'), 'M' => _('Midi'),        'S' => _('Mini'),  'T' => ''), // separate courses
                array('L' => _('Standard'), 'M' => _('Midi+Mini'),   'S' => '',         'T' => ''), // mixed courses
                array('L' => _('Std+Midi+Mini'), 'M' => '',               'S' => '',         'T' => '') // common
            ),
            'Modes' => array(array(/* separado */ 0, 1, 2, -1), array(/* mixto */ 0, 3, 3, -1), array(/* conjunto */ 4, 4, 4, -1 )),
            'ModeStrings' => array( // text to be shown on each category
                array(/* separado */ "Standard", "Midi", "Mini", "Invalid"),
                array(/* mixto */ "Standard", "Midi+Mini", "Midi+Mini", "Invalid"),
                array(/* conjunto */ "Conjunta", "Conjunta", "Conjunta", "Invalid")
            ),
            'IndexedModes' => array (
                "Standard", "Midi", "Mini", "Midi+Mini", "Conjunta L/M/S", "Tiny", "Standard+Midi", "Mini+Tiny", "Conjunta L/M/S/T"
            ),
            'IndexedModeStrings' => array(
                "-" => "",
                "L"=>"Standard",
                "M"=>"Midi",
                "S"=>"Mini",
                "T"=>"Tiny", // invalid
                "LM"=>"Standard/Midi", // invalid
                "ST"=>"Mini/Tiny", // invalid
                "MS"=>"Midi/Mini",
                "LMS" => 'Conjunta LMS',
                "LMST" => 'Conjunta LMST', // invalid
                "-LMST" => '' // team4
            )
        );
    }

    /**
     * Evaluate if a dog has a mixBreed License
     * @param $lic
     */
    function validLicense($lic){
        $lic=strval($lic);
        // remove dots, spaces and dashes
        $lic=str_replace(" ","",$lic);
        $lic=str_replace("-","",$lic);
        $lic=str_replace(".","",$lic);
        $lic=strtoupper($lic);
        if (strlen($lic)<4) {
            if (is_numeric($lic)) return true; // licenses from 0 to 999
            return false;
        }
        if (strlen($lic)>4) return false; // rsce licenses has up to 4 characters
        if (substr($lic,0,1)=='0') return true; // 0000 to 9999
        if (substr($lic,0,1)=='A') return true; // A000 to A999
        if (substr($lic,0,1)=='B') return true; // B000 to B999
        if (substr($lic,0,1)=='C') return true; // C000 to C999
        return false;
    }

    /**
     * Evalua la calificacion parcial del perro
     * @param {object} $p datos de la prueba
     * @param {object} $j datos de la jornada
     * @param {object} $m datos de la manga
     * @param {array} $perro datos de puntuacion del perro. Passed by reference
     * @param {array} $puestocat puesto en funcion de la categoria
     */
    public function evalPartialCalification($p,$j,$m,&$perro,$puestocat) {
        if ($perro['Grado']!=="GIII") {
            parent::evalPartialCalification($p,$j,$m,$perro,$puestocat);
            return;
        }
        if (intval($p->Selectiva)==0) {
            parent::evalPartialCalification($p,$j,$m,$perro,$puestocat);
            return;
        }
        // arriving here means prueba selectiva and Grado III
        // comprobamos si el perro es mestizo
        if (! $this->validLicense($perro['Licencia']) ) { // perro mestizo o extranjero no puntua
            parent::evalPartialCalification($p,$j,$m,$perro,$puestocat);
            return;
        }
        // si no tiene excelente no puntua
        if ( ($perro['Penalizacion']>=6.0)) {
            parent::evalPartialCalification($p,$j,$m,$perro,$puestocat);
            return;
        }
        $pts=array("25","20","16","12","8","6","4","3","2","1"); // puntuacion manga de agility
        if (intval($m->Tipo)==11) $pts=array("18","14","11","8","6","5","4","3","2","1"); // puntuacion manga de jumping
        // solo puntuan los 10 primeros
        if ( ($puestocat[$perro['Categoria']]>10) || ($puestocat[$perro['Categoria']]<=0) ) {
            parent::evalPartialCalification($p,$j,$m,$perro,$puestocat);
            return;
        }
        // si llegamos aqui tenemos los 10 primeros perros una prueba selectiva en grado 3 con un perro no mestizo que ha sacado excelente :-)
        $pt1=$pts[$puestocat[$perro['Categoria']]-1];
        if ($perro['Penalizacion']>0)	{
            $perro['Calificacion'] = _("Exc")." - $pt1";
            $perro['CShort'] = _("Exc");
        }
        if ($perro['Penalizacion']==0)	{
            $perro['Calificacion'] = _("Exc")." (p) - $pt1";
            $perro['CShort'] = _("Ex P");
        }
    }

    /**
     * Evalua la calificacion final del perro
     * @param {object} $p datos de la prueba
     * @param {object} $j datos de la jornada
     * @param {object} $m1 datos de la primera manga
     * @param {object} $m2 datos de la segunda manga
     * @param {array} $c1 resultados de la primera manga
     * @param {array} $c2 resultados de la segunda manga
     * @param {array} $perro datos de puntuacion del perro. Passed by reference
     * @param {array} $puestocat puesto en funcion de la categoria
     */
    public function evalFinalCalification($p,$j,$m1,$m2,$c1,$c2,&$perro,$puestocat){
        $grad=$perro['Grado']; // cogemos la categoria

        if ($grad==="GI") { // en grado uno se puntua por cada manga
            $pts=0;
            if ($perro['P1']==0.0) $pts++;
            if ($perro['P2']==0.0) $pts++;
            $perro['Calificacion'] = "";
            if ($pts==1) $perro['Calificacion'] = "1 Punto";
            if ($pts==2) $perro['Calificacion'] = "2 Puntos";
            return;
        }
        if ($grad==="GII") { // grado dos puntua normalmente
            $perro['Calificacion']="";
            if ( ($perro['P1']<6.0) && ($perro['P2']<6.0) ) $perro['Calificacion']= 'P. Equipos';
            if ($perro['Penalizacion']==0.0) $perro['Calificacion']= 'Punto';
            return;
        }
        if ($grad!=="GIII") {
            return; // ignore other extrange grades
        }
        // arriving here means grado III
        if ($p->Selectiva==0){
            $perro['Calificacion']="";
            if ( ($perro['P1']<6.0) && ($perro['P2']<6.0) ) $perro['Calificacion']= 'P. Equipos';
            if ($perro['Penalizacion']==0.0) $perro['Calificacion']= 'Punto';
            return;
        }
        // arriving here means prueba selectiva and Grado III
        if ( ! $this->validLicense($perro['Licencia']) ) {  // comprobamos si el perro es mestizo o extranjero
            $perro['Calificacion'] = ($perro['Penalizacion']==0.0)?'Punto':'';
            return;
        }
        switch($perro['Categoria']){
            case 'L': $tipo=$m1->TRS_L_Tipo; $factor=$m1->TRS_L_Factor; $unit=$m1->TRS_L_Unit; break;
            case 'M': $tipo=$m1->TRS_M_Tipo; $factor=$m1->TRS_M_Factor; $unit=$m1->TRS_M_Unit; break;
            case 'S': $tipo=$m1->TRS_S_Tipo; $factor=$m1->TRS_S_Factor; $unit=$m1->TRS_S_Unit; break;
            default: return; // invalid; do not evaluate
        }
        if ( ($tipo==1) && ($factor==0)) {  // SI TRS_L_Factor es 0 tenemos puntuacion para individual
            // manga 1 - puntuan los 10 primeros en cada manga con excelente
            $pts=array("25","20","16","12","8","6","4","3","2","1"); // puntuacion manga de agility
            if (intval($m1->Tipo)==11) $pts=array("18","14","11","8","6","5","4","3","2","1"); // puntuacion manga de jumping
            $perro['C1']="";
            if ( ($perro['P1']<6.0) && ($perro['Pcat1']<=10) && ($perro['Pcat1']>0)) {
                $perro['C1']=$pts[$perro['Pcat1']-1];
            }
            // manga 2 - puntuan los 10 primeros en cada manga con excelente
            $pts=array("25","20","16","12","8","6","4","3","2","1"); // puntuacion manga de agility
            if (intval($m2->Tipo)==11) $pts=array("18","14","11","8","6","5","4","3","2","1"); // puntuacion manga de jumping
            $perro['C2']="";
            if ( ($c2!=null) && ($perro['P2']<6.0) && ($perro['Pcat2']<=10) && ($perro['Pcat2']>0)) {
                $perro['C2']=$pts[$perro['Pcat2']-1];
            }
            // conjunta - puntuan los 10 primeros si tienen doble excelente
            $pts=array("10","9","8","7","6","5","4","3","2","1"); // puntuacion manga conjunta individual
            $pfin=" ";
            if ( ($perro['P1']<6.0) && ($perro['P2']<6.0)  && ($perro['Pcat']<=10) && ($perro['Pcat']>0)) {
                $pfin=$pts[$perro['Pcat']-1];
            }
            // finalmente componemos el string a presentar
            $perro['Calificacion']= /* $str=strval($pt1)."-".strval($pt2)."-" . */ strval($pfin);
        }
        if ( ($tipo==1) && ($factor==10) && ($unit=='%') ) {  // SI TRS_L_Factor es +10% tenemos clasificacion por equipos
            // solo puntua conjunta si el perro tiene doble excelente
            $ptsteam=array("20","16","12","8","7","6","4","3","2","1"); // puntuacion manga conjunta equipos
            $pteam=" ";
            if ( ($perro['P1']<6.0) && ($perro['P2']<6.0)  && ($perro['Pcat']<=10) && ($perro['Pcat']>0) ) {
                $pteam=$ptsteam[$perro['Pcat']-1];
            }
            // finalmente componemos el string a presentar
            $perro['Calificacion']=strval($pteam);
        }
        return; // should be overriden
    }
}
?>