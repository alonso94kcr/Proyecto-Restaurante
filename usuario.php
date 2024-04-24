<?php
    $servidor = "localhost";
    $usuario = "root";
    $clave = "";
    $nombreBaseDatos = "restaurante";

    // Conectarse a la base de datos
    $conn = new mysqli($servidor, $usuario, $clave, $nombreBaseDatos);

    // Establecer el juego de caracteres UTF-8 en la conexión
    mysqli_set_charset($conn,"utf8");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    // Establecer la configuración de cabecera HTTP adecuada
    header('Content-Type: application/json');

    // Leer la solicitud HTTP y determinar el tipo de operación
    $method = $_SERVER['REQUEST_METHOD'];
    // echo $method;

    // Realizar la operación correspondiente en función del tipo de solicitud
    switch ($method) {
        case 'GET':            
            if (!isset($_GET['Id'])) {                
                // Obtener todos los registros                 
                $result = $conn -> query("SELECT * FROM clientes");
    
                // Crear un array para almacenar los registros seleccionados
                $elementos = array();
    
                // Iterar sobre cada fila de resultados y almacenar los campos en el array
                while ($row = $result->fetch_assoc()) {
                    $elementos[] = $row;
                }
    
                // Devolver los campos en formato JSON
                echo json_encode($elementos);
                break;
            }

            // Obtener un registro en particular
            $id = intval($_GET['id']);
            $result = $conn -> query("SELECT * FROM clientes WHERE Nombres=$id");
            // Crear un array para almacenar el registro seleccionados
            $elementos = array();
            // Iterar sobre cada fila de resultados y almacenar los campos en el array
            while ($row = $result->fetch_assoc()) {
                    $elementos[] = $row;
            }
            // Devolver los campos en formato JSON
            echo json_encode($elementos);
            break;

        case 'POST':
            if (!isset($_GET["opcion"])){
                // Leer el cuerpo de la solicitud y decodificarlo como JSON
                $input = json_decode(file_get_contents('php://input'), true);

                // Sanitizar los campos
                $nombre = $input['Nombre'];
                // $nombre = filter_var($nombre, FILTER_SANITIZE_STRING);

                $correo = $input['Email'];
                $correo = filter_var($correo, FILTER_SANITIZE_EMAIL);

                // Insertar un nuevo registro en la base de datos
                $sql = "INSERT INTO clientes (Nombre, Email) VALUES (?, ?)";
            
                $query = $conn->prepare($sql);
                $query->bind_param("ss", $nombre, $correo);
                $query->execute();
                // Devolver el ID del registro insertado
                $data = array('estado' => true,
                'id' => $conn->insert_id);
                die(json_encode($data));
                break;
            }

            // Leer el cuerpo de la solicitud y decodificarlo como JSON
            $input = json_decode(file_get_contents('php://input'), true);

            // Sanitizar los campos
            $nombre = $input['Nombre'];
            // $nombre = filter_var($nombre, FILTER_SANITIZE_STRING);

            $correo = $input['Email'];
            $correo = filter_var($correo, FILTER_SANITIZE_EMAIL);

            // Actualizar el registros en la base de datos
            $sql = "UPDATE clientes SET Nombre = ?, Email = ? WHERE Id = ?";
            $query = $conn->prepare($sql);
            $query->bind_param("ssi", $nombre, $correo, $input['Id']);
            $query->execute();

            // Devolver un código de estado de éxito   
            // http_response_code(200);
            $data = array('estado' => true);
            die(json_encode($data));
            break;

        case 'DELETE':
            // Leer el ID del registros a eliminar de la URL
            $id = intval($_GET['Id']);
            // Eliminar el registros de la base de datos
            $sql = "DELETE FROM clientes WHERE Id = ?";
            $query = $conn->prepare($sql);
            $query->bind_param("I", $id);
            $query->execute();
            // Devolver un código de estado de éxito
            http_response_code(200);
            $data = array('estado' => true);
            die(json_encode($data));
            break;
        
        default:
            # code...
            break;
    }

    // Cerrar la conexión a la base de datos
    $conn->close();