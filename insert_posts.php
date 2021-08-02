<?php
include('wp-config.php');
define( 'WP_CACHE', false );

/**
 * Script para importar dados de um csv e inserir paginas do wordpress
 * Eh necessario fazer os ajustes dos inserts de acordo com as colunas do arquivo.
 */

function conectarMysql(){	
	$conMYSQL = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (mysqli_connect_errno()) {
        die("15 - Erro ao conectar ao banco de dados: " . mysqli_connect_error());
    }
    
    return $conMYSQL;
}

/* cria o link da pagina */
function criarLink($assunto){
	$tituloLink = utf8_decode($assunto);
	$array1 = utf8_decode("ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ:.*+,()&%#$@!?ºª ");
	$array2 = utf8_decode("aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyyby________________");
	$array3 = utf8_decode("/\\.(?![^.]*$)/");	
	$tituloLink = preg_replace($array3,'',$tituloLink) ;
	$tituloLink = strtr($tituloLink,$array1,$array2);
	$tituloLink = str_replace(" ","-",$tituloLink);
	$tituloLink = str_replace("_","-",$tituloLink);
	$tituloLink = str_replace("__","-",$tituloLink);
	$tituloLink = strtolower($tituloLink);	
	$tituloLink = utf8_encode($tituloLink);
    return $tituloLink;
}

$con = conectarMysql();

$handle = fopen("base.csv", "r"); //nome do arquivo, colocar no mesmo diretorio
$postIdTemp = 13425; //proximo id da table post
$postType = "pesquisadores";
$urlSite = "https://imdp.com.br";

echo "<pre>";
while (($line = fgetcsv($handle, 1000, ";")) !== FALSE) {
	
    $postTitle = utf8_encode($line[1]);
    $postName = criarLink($postTitle);

    $sqlPost = "INSERT INTO `".$table_prefix."posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
    (NULL, 6, '2021-07-14 20:59:59', '2021-07-14 23:59:59', '".utf8_encode($line[10])."', '".$postTitle."', '', 'publish', 'closed', 'closed', '', '".$postName."', '', '', '2021-07-14 20:59:59', '2021-07-14 23:59:59', '', 0, '".$urlSite."/?post_type=".$postType."&#038;p=".$postIdTemp."', 0, '".$postType."', '', 0);";
	mysqli_query($con, $sqlPost);
	$postId = mysqli_insert_id($con);
	$postIdTemp = $postId + 1;
	echo $postId." - Página ".$postTitle." criada<br/>";

	$sqlPostmeta = "INSERT INTO ".$table_prefix."postmeta (post_id, meta_key, meta_value) values
		(".$postId.", 'digiqole_featured_post', 'no'),
		(".$postId.", '_edit_lock', '1625706178:6'),
		(".$postId.", '_edit_last', '6'),
		(".$postId.", '_nome', '".utf8_encode($line[1])."'),
		(".$postId.", '_email', '".utf8_encode($line[2])."'),
		(".$postId.", '_site', '".utf8_encode($line[3])."'),
		(".$postId.", '_cidade', '".utf8_encode($line[8])."'),
		(".$postId.", '_estado', '".utf8_encode($line[9])."'),
		(".$postId.", '_titulacao', '".utf8_encode($line[11])."'),
		(".$postId.", '_instituicao', '".utf8_encode($line[12])."'),
		(".$postId.", '_area-de-atuacao', '".utf8_encode($line[13])."'),
		(".$postId.", '_curriculo-lattes', '".utf8_encode($line[14])."'),
		(".$postId.", 'ekit_post_views_count', '1')";
	mysqli_query($con, $sqlPostmeta);
	echo $sqlPostmeta." - Postmeta inserido<br/>";
}
echo "</pre>";

mysqli_close($con);
fclose($handle);