<?php

namespace App\Http\Controllers;

class ImageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

     //TODO configurar parametros, etc
    function receive(){
        $dir = '/var/www/html/arquivos/';
        $files = scandir($dir);

        foreach($files as $key => $value){
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(is_dir($path) && $value != "." && $value != "..") {
                echo "<div class=\"body\">Arquivos de Origem da Estação ".$value."</div><br />";
                $files_st = scandir($dir.DIRECTORY_SEPARATOR.$value);
                $class="bodywt";
                foreach($files_st as $key_st => $value_st){
                    $path_st = realpath($dir.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR.$value_st);
                    if(!is_dir($path_st)) {
                        $file_ext = pathinfo($path_st, PATHINFO_EXTENSION);
                        if ($class == "bodywt"){
                            echo "<div class=\"bodywt\">";
                            $class="body";
                        } else {
                            echo "<div class=\"body\">";
                            $class="bodywt";
                        }
                        if ($file_ext=="gpg") {
                            echo $value_st;
                            echo "<form action=\"decrypt.php\" method=\"post\" enctype=\"multipart/form-data\" style=\"display: inline;\">";
                            echo "<br />Senha: ";
                            echo "<input type=\"text\" name=\"password\" />";
                            echo "<input type=\"submit\" value=\"Abrir com Senha\" name=\"submit\" />";
                            echo "<input type=\"hidden\" name=\"path\" value=\"".$path_st."\" />";
                            echo "</form>";
                        }
                        else {
                            echo "<a href=\"arquivos/".$value."/".$value_st."\">".$value_st."</a>";
                        }
                        // echo "<br />";
                        echo "</div>";
    }
                    // $results[] = $path;
                }
            }
        }

        if ($class == "bodywt"){
            echo "<div class=\"bodywt\">";
        } else {
            echo "<div class=\"body\">";
        }

        echo "<a href=\"clean_files.php\"><i class=\"material-icons\">cancel</i>
    Limpar Todos Arquivos</a>";

        echo "</div>";
    }

    function upload(){
        $target_dir = "/var/www/html/uploads/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $remote_dir = "/var/www/html/arquivos/";
        $uploadPic = 0;
        $uploadOk = 1;
        $file_in_place = 0;

        // IMAGE SECTION //
        $source = substr ($_POST['myname'], 0,  6);

        if ($source == $_POST['prefix'])
        {
            echo "ERRO: Estação de origem é igual estação de destino! <br />";
            exit;
        }

        $cmd= "alias.sh ".substr ($_POST['myname'], 0,  6);
        $source = shell_exec($cmd);
        // echo $cmd." <br />";
        if ($source == $_POST['prefix'])
        {
            echo "ERRO: Estação de origem é igual estação de destino! <br />";
            exit;
        }

        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false) {
                //        echo "File is an image - " . $check["mime"] . ".";
                $uploadPic = 1;
                $uploadOk = 1;
            } else {
                //        echo "File is not an image. Proceding normally";
                $uploadOk = 1;
                $uploadPic = 0;
            }
        }
        // Check if file already exists
        if (file_exists($target_file)) {
            //    echo "Sorry, file already exists, cotinuing...";
            $uploadOk = 1;
        }

        // Image but not jpg case
        if($imageFileType != "jpg" && $imageFileType != "JPG" && $imageFileType != "jpeg"
           && $imageFileType != "JPEG" && $uploadPic == 1) {
            if (($_FILES["fileToUpload"]["size"] > 50*1024) && $uploadPic == 1 ) {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file))
                {
                    $arr = explode("." . $imageFileType, $target_file);
                    $new_target = $arr[0] . ".jpg";
                    $command = "compress_image.sh \"" .  $target_file . "\" \"" . $new_target . "\"";
                    //          echo "Command: " . $command . "<br />";
                    ob_start();
                    system($command , $return_var);
                    ob_end_clean();
                    unlink($target_file);
                    $target_file = $new_target;
                    $uploadOk = 1;
                    $file_in_place = 1;
                } else {
                    $uploadOk = 0;
                    echo "Erro ao mover o arquivo para pasta temporária. <br />";
                }
            }
        }

        // Check file size and if it is picture, reduce the size...
        // limit not to reduce size is 50k!
        if (($_FILES["fileToUpload"]["size"] > 50*1024) && $uploadPic == 1 && $file_in_place == 0 && $uploadOk == 1) {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                $command = "compress_image.sh \"" .  $target_file . "\"";
                //        echo "Command: " . $command . "<br />";
                ob_start();
                system($command , $return_var);
                ob_end_clean();
                $uploadOk = 1;
                $file_in_place = 1;
            } else {
                $uploadOk = 0;
                echo "Erro ao mover o arquivo para pasta temporária. <br />";
            }
        }

        // Check file size of a normal file.
        // limit is 50k!
        if (($_FILES["fileToUpload"]["size"] > 50*1024) && $uploadPic == 0 ) { // 10MB max
            echo "Arquivo muito grande. Máximo permitido: 51200 bytes, tamanho do arquivo: " . $_FILES["fileToUpload"]["size"] . " bytes.<br />";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            //    echo "Erro no pré-processamento do arquivo.<br />";
            // if everything is ok, try to upload file
        } else {
            if ($file_in_place == 0)
            {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                    $file_in_place = 1;
                }
                else {
                    echo "Erro ao mover o arquivo para pasta temporária. <br />";
                    $uploadOk = 0;
                }
            }

            if (isset($_POST['encrypt']) && $file_in_place == 1)
            {
                $command = "encrypt.sh \"" . $target_file . "\" \"" . $_POST['password'] . "\"";
                //           echo "encrypt command: " . $command . "<br />";
                ob_start();
                system($command , $return_var);
                $output = ob_get_contents();
                ob_end_clean();
                unlink($target_file);
                $target_file = $target_file . ".gpg";
                //           echo "Criptografia ativada!<br />";
            }

            if ($file_in_place == 1) {
                if (isset($_POST['sendnow']))
                {
                    $command = "uucp -C -d \"" .  $target_file . "\" " . $_POST['prefix'] . "\!\"" . $remote_dir . $source . "/\"";
                    echo "Arquivo <b>".basename($target_file)."</b> adicionado com sucesso e transmissão iniciada.<br />";
                } else
                {
                    $command = "uucp -r -C -d \"" .  $target_file . "\" " . $_POST['prefix'] . "\!\"" . $remote_dir . $source . "/\"";
                    echo "Arquivo <b>".basename($target_file)."</b> adicionado com sucesso.<br />";
                }
                //           echo "UUCP Command: " . $command . "<br />";
                ob_start();
                system($command , $return_var);
                $output = ob_get_contents();
                ob_end_clean();
            }
        }
        unlink($target_file);
    }
}

function exec_cli($command = "ls -l")
    {
        ob_start();
        system($command , $return_var);
        $output = ob_get_contents();
        ob_end_clean();

        //or die;
        /*if ($exploder==true){
                return (explode("\n", $output));
                }*/

        return ($output);

    }
