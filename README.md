# Plugin Object3D for Pawtucket

## Install

- Install the required database profile or add required metadatas (doc to come)
- Download and install this project directory inside pawtucket2/app/plugins
- Modify the ca_objects_default_html.php inside your theme dir following the example

## Modifying ca_objects_default_html.php

````php
    <?php
    require_once(__CA_APP_DIR__."/plugins/Object3D/Object3D.php");
    $object_id = $t_object->get("object_id");
    Object3D::Viewer($object_id);
    ?>

````