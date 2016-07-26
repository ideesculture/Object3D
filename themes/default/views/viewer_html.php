<?php
require_once(__CA_APP_DIR__.'/models/ca_objects.php');



$id_object = $_GET["id"];
$vt_object = new ca_objects($id_object);

$path3ddisplay = $vt_object->get("ca_objects.fichier3d");
if (!$path3ddisplay) die("Pas d'objet 3D ici, veuillez vérifier l'ID transmis.");
$path3dfilename = reset(explode("\n", strip_tags(str_replace("</div>", "</div>\n", $path3ddisplay))));
$foldername = str_replace(".zip","",$path3dfilename);
$path3d = $vt_object->get("ca_objects.fichier3d", array("return"=>"path"));
$path_parts = pathinfo($path3d);
$path = str_replace("//media/".__CA_APP_NAME__, "", $path_parts['dirname']);
$res = $path."/".$path_parts['filename'];
$load = true;
if(filesize($res)>10240){
	$load=false;
	die("l'objet à afficher est trop lourd et pourrait faire planter votre navigateur");
}
$folderurl = __CA_URL_ROOT__.str_replace(__CA_BASE_DIR__,"", $res)."/".$foldername;
//var_dump($res);
if(is_dir($res)){
	if(is_file($res."/".pathinfo($path3dfilename)['filename'].".obj")){
		print 'l obj a le bon nom';
	}
	exec('cd '.$res.'/');
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

<script src="<?php print __CA_URL_ROOT__."/app/plugins/Object3D/lib/"; ?>/Threejs/build/three.js"></script>

<script src="<?php print __CA_URL_ROOT__."/app/plugins/Object3D/lib/"; ?>Threejs/js/loaders/DDSLoader.js"></script>
<script src="<?php print __CA_URL_ROOT__."/app/plugins/Object3D/lib/"; ?>Threejs/js/loaders/MTLLoader.js"></script>
<script src="<?php print __CA_URL_ROOT__."/app/plugins/Object3D/lib/"; ?>Threejs/js/loaders/OBJLoader.js"></script>

<script src="<?php print __CA_URL_ROOT__."/app/plugins/Object3D/lib/"; ?>Threejs/js/Detector.js"></script>
<script src="<?php print __CA_URL_ROOT__."/app/plugins/Object3D/lib/"; ?>Threejs/js/libs/stats.min.js"></script>
<script src="<?php print __CA_URL_ROOT__."/app/plugins/Object3D/lib/"; ?>Threejs/js/OrbitControls.js"></script>
<script src="<?php print __CA_URL_ROOT__."/app/plugins/Object3D/lib/"; ?>Threejs/js/TrackballControls.js"></script>
<style>

#preload {

    display: block;
    width: 120px;
    text-align: center;
    font-family: helvetica, arial;
    background-color: #333;
    padding: 40px;
    border-radius: 24px;
    margin: 120px auto 120px auto;

}

</style>
<head>
	<script src="../../../node_modules/jquery/dist/jquery.min.js"></script>
</head>

<h2><?php print $foldername ?></h2>
<p id="preload"></p>


<script>

			var container, stats;

			var camera, scene, renderer;

			var controls;

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

				controls = new THREE.TrackballControls( camera );

				controls.rotateSpeed = 11.0;
				controls.zoomSpeed = 1.2;
				controls.panSpeed = 0.8;

				controls.noZoom = false;
				controls.noPan = false;

				controls.staticMoving = true;
				controls.dynamicDampingFactor = 0.3;

				controls.keys = [ 65, 83, 68 ];

				controls.addEventListener( 'change', render );


				// scene

				scene = new THREE.Scene();

				var ambient = new THREE.AmbientLight( 0x444444 );
				scene.add( ambient );

				var directionalLight = new THREE.DirectionalLight( 0xffeedd );
				directionalLight.position.set( 0, 0, 1 ).normalize();
				scene.add( directionalLight );

				var isloaded = false;
				var preload = document.getElementById("preload");

				if(<?php print $load ?> == true && isloaded == false){
					preload.textContent = "Click anywhere to load the image";
					window.onclick = loadFunction;
				}
				// model

				var onProgress = function ( xhr ) {
					if ( xhr.lengthComputable ) {
						var percentComplete = xhr.loaded / xhr.total * 100;
						preload.textContent = Math.round(percentComplete, 2) + '% downloaded';
						console.log( Math.round(percentComplete, 2) + '% downloaded' );
					}
				};


				var onError = function ( xhr ) { };

				THREE.Loader.Handlers.add( /\.dds$/i, new THREE.DDSLoader() );

				var mtlLoader = new THREE.MTLLoader();
				
				
				//mtlLoader.setBaseUrl('<?php print $folderurl; ?>');
				

				function loadFunction(){
					if(isloaded == false){
						mtlLoader.setPath('http://pawtucket.dev/<?php print $folderurl; ?>/'); //récupérer le path vers le dossier qui contient les fichiers
						mtlLoader.load('<?php print $foldername; ?>.mtl', function( materials ) { //récupérer le nom du mtl
							materials.preload();
							var objLoader = new THREE.OBJLoader();
							objLoader.setMaterials( materials );
							//objLoader.setBaseUrl('<?php print $folderurl; ?>');
							objLoader.setPath('http://pawtucket.dev/<?php print $folderurl; ?>/'); //récupérer le path vers le dossier qui contient les fichiers
							objLoader.load('<?php print $foldername; ?>.obj', function ( object ) { //récupérer le nom de l'object

								object.position.y = 0;
								scene.add( object );
								preload.style.display = "none";

							}, onProgress, onError );

						});	
					}
					isloaded = true;
					
				}
				//

				renderer = new THREE.WebGLRenderer();
				renderer.setPixelRatio( window.devicePixelRatio );
				renderer.setSize( window.innerWidth, window.innerHeight );
				container.appendChild( renderer.domElement );

				//document.addEventListener( 'mousemove', onDocumentMouseMove, false );

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

			//function onDocumentMouseMove( event ) {

			//	mouseX = ( event.clientX - windowHalfX ) / 2;
			//	mouseY = ( event.clientY - windowHalfY ) / 2;

			//}

			//

			function animate() {

				requestAnimationFrame( animate );
				controls.update();
				render();

			}

			function render() {

				//camera.position.x += ( mouseX - camera.position.x ) * .05;
				//camera.position.y += ( - mouseY - camera.position.y ) * .05;

				camera.lookAt( scene.position );

				renderer.render( scene, camera );

			}

		</script>
