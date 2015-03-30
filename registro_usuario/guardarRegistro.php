<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<?php

    //Recuperar los datos del formulario de registro
    $str_nombre         =trim($_POST['tx_nombre']);

    $str_correo         =trim($_POST['tx_correo']);
    $str_username       =trim($_POST['tx_username']);
    $str_password       =trim($_POST['tx_password']);
    $str_password2  =trim($_POST['tx_password2']);


    //Devuelve true si la cadena que llega esta VACIA
    function estaEnBlanco($cadena) {
        if(strlen( trim($cadena) ) == 0 )
            return true;
        return false;
    }

    //Devuelve true si la longitud de la cadena (primer parametro)
    // que llega  es menor que el numero (segundo parametro)
    function validaTamanio($cadena,$longitud) {
        if(strlen( trim($cadena) ) < $longitud )
            return true;
        return false;
    }

    // devuelve true SI ha escrito, un email NO VALIDO
    function esCorreoInvalido($str_email) {
        if(!filter_var(trim($str_email), FILTER_SANITIZE_EMAIL))
            return true;
        return false;
    }

    // devuelve una cadena escapada de algunos caracteres que
    // pudieran servir para un ataque de sql injection
    function escaparQuery($cadena) {
        $str_KeywordsSQL            = array("select ","insert ","delete ","update ","union ");
        $str_OperadoresSQL      = array("like ","and ","or ","not ","<",">","<>","=","<");
        $str_DelimitadoresSQL = array(";","(",")","'");

        //Quitar palabras reservadas y operadores
        for($i=0; $i<count($str_KeywordsSQL); $i++) {
            $cadena = str_replace($str_KeywordsSQL[$i], "",strtolower($cadena) );
        }
        for($i=0; $i<count($str_OperadoresSQL); $i++) {
            $cadena = str_replace($str_OperadoresSQL[$i], "",strtolower($cadena) );
        }
        for($i=0; $i<count($str_DelimitadoresSQL); $i++) {
            $cadena = str_replace($str_DelimitadoresSQL[$i], "",strtolower($cadena) );
        }

        return $cadena;
    }


    $mensajesAll= "";

    //Mensajes para el nombre
    if( estaEnBlanco($str_nombre) )
        $mensajesAll = "<li>Por favor, escriba su Nombre.</li>";
    if( validaTamanio($str_nombre,3) )
        $mensajesAll .= "<li>Su Nombre como minimo debe tener 3 caracteres.</li>";

    //Mensajes para el Correo electronico
    if( estaEnBlanco($str_correo) || validaTamanio($str_correo,5) || esCorreoInvalido($str_correo) )
        $mensajesAll .= "<li>Por favor, escriba una direccion de correo electronico valida.</li>";
    //Mensajes para el nombre de usuario
    if( estaEnBlanco($str_username) )
        $mensajesAll .= "<li>Por favor, escriba un nombre de usuario. Este dato le servira para iniciar sesion y ver el contenido.</li>";
    if( validaTamanio($str_username,5) )
        $mensajesAll .= "<li>Su nombre de usuario como minimo debe tener 5 caracteres.</li>";
    //Mensajes para el password
    if( estaEnBlanco($str_password) )
        $mensajesAll .= "<li>Por favor, escriba una contrase&ntilde;a.</li>";
    if( validaTamanio($str_password,5) )
        $mensajesAll .= "<li>Su contrase&ntilde;a como minimo debe tener 5 caracteres.</li>";
    //Mensajes para la confirmacion del password
    if( estaEnBlanco($str_password2) || validaTamanio($str_password2,5) )
        $mensajesAll .= "<li>Por favor, confirme la contrase&ntilde;a anterior.</li>";
    if( trim($str_password) != trim($str_password2) )
        $mensajesAll .= "<li>Por favor, repita la contrase&ntilde;a anterior.</li>";


    $log = $mensajesAll."<br>";

    //Si se generaron mensajes de error al validar...
    if ( trim($mensajesAll) != "" ) {
        //..Redireccion a la pagina de registro para mostrar msg de error al usuario
        //Enviar los datos que habia escrito antes de enviar
    ?>
    <form id="frm_error"   name="frm_error" method="post" action="registro.php">
        <input type="hidden" name="error" value="1" />
        <input type="hidden" name="msgs_error" value='<?php echo $mensajesAll ?>' />
        <input type="hidden" name="str_nombre" value='<?php echo $str_nombre ?>' />

        <input type="hidden" name="str_correo" value='<?php echo $str_correo ?>' />
        <input type="hidden" name="str_username" value='<?php echo $str_username ?>' />
        <input type="hidden" name="str_password" value='<?php echo $str_password ?>' />
        <input type="hidden" name="str_password2" value='<?php echo $str_password2 ?>' />
    </form>
    <script type="text/javascript">
        //Redireccionar con el formulario creado
        document.frm_error.submit();
    </script>
<?php
        exit;
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Registro de Usuarios</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script src="jquery171.js" type="text/javascript"></script>
    <script src="jquery.validate.js" type="text/javascript"></script>
    <script type="text/javascript" src="jquery.alerts.js"></script>
    <link href="jquery.alerts.css" rel="stylesheet" type="text/css" />

    <script type="text/javascript">
    <!--
        $().ready(function() {

        });
    // -->
    </script>

</head>
<body>

<?php
    $mensajesAll                = "";
    $username_duplicado = false;
    $email_duplicado        = false;
    //Escapar las cadenas para avitar SQL Injection
    $str_username = escaparQuery($str_username);
    $str_correo     = escaparQuery($str_correo);

    //Conectar la BD
    include("conectarlogin.php");
    conectar_bd();

    //Validar que el nombre de usuario no exista en la BD
    $sql = "SELECT  id  FROM tbl_users
    WHERE tx_username = '".trim($str_username)."';";
    $rs_sql = mysql_query($sql);
    $log .=  $sql."<br>";

    //Si ya existe el usuario en la BD...
    if ( $fila  = mysql_fetch_object($rs_sql) ) {
        $mensajesAll = "<li>El nombre de usuario <b>".$str_username."</b> ya fue registrado
        por otra persona. Por favor, escriba otro.</li>";
        $username_duplicado = true;
    }

    //Validar que el email no exista en la BD
    $sql = "SELECT  id  FROM tbl_users
    WHERE tx_correo='".$str_correo."';";
    $rs_sql = mysql_query($sql);
    $log .=  $sql."<br>";


    //Si ya existe el email en la BD...
    if ( $fila  = mysql_fetch_object($rs_sql) ) {
        $mensajesAll = "<li>El correo electronico <b>".$str_correo."</b> ya fue registrado
        por otra persona. Por favor, escriba otro.</li>";
        $email_duplicado        = true;
    }
    //Si ambos datos ya estan en la Base de datos mostrar un solo msg
    if( $username_duplicado && $email_duplicado)
        $mensajesAll = "<li>Ambos, nombre de usuario <b>".$str_username."</b>
        y correo electronico <b>".$str_correo."</b> ya fueron registrados por otra persona.
        Por favor, cambie esos datos.</li>";
    //..Redireccion a la pagina de registro para mostrar msg de error al usuario
    //Enviar los datos que habia escrito antes de enviar

    $log .=  $mensajesAll."<br>";


    if ( trim($mensajesAll) != "" ) {
        //..Redireccion a la pagina de registro para mostrar msg de error al usuario
        //Enviar los datos que habia escrito antes de enviar
        ?>
        <form id="frm_error"   name="frm_error" method="post" action="registro.php">
            <input type="hidden" name="error" value="2" />
            <input type="hidden" name="msgs_error" value='<?php echo $mensajesAll ?>' />
            <input type="hidden" name="str_nombre" value='<?php echo $str_nombre ?>' />

            <input type="hidden" name="str_correo" value='<?php echo $str_correo ?>' />
            <input type="hidden" name="str_username" value='<?php echo $str_username ?>' />
            <input type="hidden" name="str_password" value='<?php echo $str_password ?>' />
            <input type="hidden" name="str_password2" value='<?php echo $str_password2 ?>' />
        </form>
        <script type="text/javascript">
            //Redireccionar con el formulario creado
            document.frm_error.submit();
        </script>
            <?php
        exit;
    }


    //..Si llega asta aqui es que todos los datos son validos, procedemos a darlo de alta en BD
    $str_elNombre               = $str_nombre." ";
    $str_elNombreCompleto = $str_nombre." ";


    //Formar el query para el insert del nuevo usuario
    $queryInsert="INSERT INTO tbl_users (tx_nombre,tx_correo,tx_username,tx_password) VALUES ('".$str_nombre."','".$str_correo."','".$str_username."','".md5($str_password)."');";

    $log .=  $queryInsert."<br>";

    //echo $log;
    //exit;

    mysql_query($queryInsert);



    //Cerrrar conexion a la BD
    mysql_close($conexio);

    // Mostrar resultado del registro

	$ruta_servidor='../carpeta_usuarios';
	$ruta_usuario=$str_username;
	$ruta_final=$ruta_servidor.'/'.$ruta_usuario;

	if(!file_exists($ruta_final)){
		mkdir($ruta_final,775);}
	else{
		echo "problema de carpeta, error ql";
		}

		if($queryInsert){
			echo"<script type=\"text/javascript\">alert('Usted esta siendo redireccionado a la pagina principal'); window.location='../index.php';</script>";
			}


        ?>
        <!--  redireccion a index despues del registro-->
    <!--<form id="frm_registro_status"   name="frm_registro_status" method="post" action="../index.php">
        <input type="hidden" name="status_registro" value="1" />
        <input type="hidden" name="i_EmailEnviado" value='<?php /*echo $i_EmailEnviado*/ ?>' />
    </form>-->
    <script type="text/javascript">
        //Redireccionar con el formulario creado
        document.frm_registro_status.submit();
    </script>
</body>
</html>