<?php
require_once(__CA_APP_DIR__.'/models/ca_objects.php');

$id_object = 26;
$vt_object = new ca_object($id_object);

$path3ddisplay = $vt_object->get("ca_objects.fichier3d");
$path3dfilename = reset(explode("\n", strip_tags(str_replace("</div>", "</div>\n", $path3ddisplay))));
$path3d = $vt_object->get("ca_objects.fichier3d", array("return"=>"path"));
$path_parts = pathinfo($path3d);
$path = str_replace("//media/".__CA_APP_NAME__, "", $path_parts['dirname']);
$res = $path."/".$path_parts['filename'];

if(is_dir($res)){
	if(is_file($res."/".pathinfo($path3dfilename)['filename'].".obj")){
		print 'l obj a le bon nom';
	}
	exec('cd '.$res.'/');
	$dir = $res.'/';
	$files = scandir($dir);
	print_r($files);
}
else{
	if($path_parts['extension'] == 'zip'){
		$zip = new ZipArchive;
		exec('cd '.$path);
		$test = $zip->open($path.'/'.$path_parts['basename'], ZIPARCHIVE::CREATE || ZIPARCHIVE::OVERWRITE);
		if($test === TRUE){
			exec('cd '.$path.' && mkdir '.$path_parts['filename'].' && chmod 777 '.$path_parts['filename'].'/');
			$extract = $zip->extractTo($res.'/');
			$ferme = $zip->close();
			$dir = $res.'/';
			$files = scandir($dir);
			print_r($files);
			exec('cd '.$path.' && chmod -R 777 *');
		}
		else{
			echo '&eacutechec <br/>'.$test.'<br/>';
		}
	}
	elseif ($path_parts['extension'] == 'rar') {
		exec('cd '.$path.' && mkdir '.$path_parts['filename'].' && chmod -R 777 *');
		$rar = rar_open($path.'/'.$path_parts['basename']);
		$list = rar_list($rar);
		foreach($list as $file){
			$entry = rar_entry_get($rar, $file->getName());
			$entry->extract($res.'/');
		}
		rar_close($rar);
		echo 'ok_rar';
		exec('cd '.$path.' && chmod -R 777 *');
	}
}



?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<title>three.js webgl - OBJLoader + MTLLoader</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<style>
			body {
				font-family: Monospace;
				background-color: #000;
				color: #fff;
				margin: 0px;
				overflow: hidden;
			}
			#info {
				color: #fff;
				position: absolute;
				top: 10px;
				width: 100%;
				text-align: center;
				z-index: 100;
				display:block;
			}
			#info a, .button { color: #f00; font-weight: bold; text-decoration: underline; cursor: pointer }
		</style>
	</head>

	<body>

		<script src="Threejs/build/three.js"></script>

		<script src="Threejs/js/loaders/DDSLoader.js"></script>
		<script src="Threejs/js/loaders/MTLLoader.js"></script>
		<script src="Threejs/js/loaders/OBJLoader.js"></script>

		<script src="Threejs/js/Detector.js"></script>
		<script src="Threejs/js/libs/stats.min.js"></script>

		<script>

			var container, stats;

			var camera, scene, renderer;

			var mouseX = 0, mouseY = 0;

			var windowHalfX = window.innerWidth / 2;
			var windowHalfY = window.innerHeight / 2;


			init();
			animate();


			function init() {

				container = document.createElement( 'div' );
				document.body.appendChild( container );

				camera = new THREE.PerspectiveCamera( 45, window.innerWidth / window.innerHeight, 1, 2000 );
				camera.position.z = 250;

				// scene

				scene = new THREE.Scene();

				var ambient = new THREE.AmbientLight( 0x444444 );
				scene.add( ambient );

				var directionalLight = new THREE.DirectionalLight( 0xffeedd );
				directionalLight.position.set( 0, 0, 1 ).normalize();
				scene.add( directionalLight );

				// model

				var onProgress = function ( xhr ) {
					if ( xhr.lengthComputable ) {
						var percentComplete = xhr.loaded / xhr.total * 100;
						console.log( Math.round(percentComplete, 2) + '% downloaded' );
					}
				};

				var onError = function ( xhr ) { };

				THREE.Loader.Handlers.add( /\.dds$/i, new THREE.DDSLoader() );

				var mtlLoader = new THREE.MTLLoader();
				mtlLoader.setPath( '32848_ca_attribute_values_value_blob_126/' ); //récupérer le path vers le dossier qui contient les fichiers
				mtlLoader.load( 'crapeau3D.mtl', function( materials ) { //récupérer le nom du mtl

					materials.preload();

					var objLoader = new THREE.OBJLoader();
					objLoader.setMaterials( materials );
					objLoader.setPath( '32848_ca_attribute_values_value_blob_126/' ); //récupérer le path vers le dossier qui contient les fichiers
					objLoader.load( 'crapeau3D.obj', function ( object ) { //récupérer le nom de l'obj

						object.position.y = - 95;
						scene.add( object );

					}, onProgress, onError );

				});

				//

				renderer = new THREE.WebGLRenderer();
				renderer.setPixelRatio( window.devicePixelRatio );
				renderer.setSize( window.innerWidth, window.innerHeight );
				container.appendChild( renderer.domElement );

				document.addEventListener( 'mousemove', onDocumentMouseMove, false );

				//

				window.addEventListener( 'resize', onWindowResize, false );

			}

			function onWindowResize() {

				windowHalfX = window.innerWidth / 2;
				windowHalfY = window.innerHeight / 2;

				camera.aspect = window.innerWidth / window.innerHeight;
				camera.updateProjectionMatrix();

				renderer.setSize( window.innerWidth, window.innerHeight );

			}

			function onDocumentMouseMove( event ) {

				mouseX = ( event.clientX - windowHalfX ) / 2;
				mouseY = ( event.clientY - windowHalfY ) / 2;

			}

			//

			function animate() {

				requestAnimationFrame( animate );
				render();

			}

			function render() {

				camera.position.x += ( mouseX - camera.position.x ) * .05;
				camera.position.y += ( - mouseY - camera.position.y ) * .05;

				camera.lookAt( scene.position );

				renderer.render( scene, camera );

			}

		</script>

	</body>
</html>
