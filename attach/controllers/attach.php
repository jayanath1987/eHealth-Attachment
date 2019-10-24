<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  --------------------------------------------------------------------------------
  HHIMS - Hospital Health Information Management System
  Copyright (c) 2011 Information and Communication Technology Agency of Sri Lanka
  <http: www.hhims.org/>
  ----------------------------------------------------------------------------------
  This program is free software: you can redistribute it and/or modify it under the
  terms of the GNU Affero General Public License as published by the Free Software
  Foundation, either version 3 of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,but WITHOUT ANY
  WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
  A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License along
  with this program. If not, see <http://www.gnu.org/licenses/>




  ----------------------------------------------------------------------------------
  Date : June 2016
  Author: Mr. Jayanath Liyanage   jayanathl@icta.lk

  Programme Manager: Shriyananda Rathnayake
  URL: http://www.govforge.icta.lk/gf/project/hhims/
  ----------------------------------------------------------------------------------
 */

session_start();

class Attach extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->model("mpersistent");
    }

    public function index() {
        return;
    }

    public function portrait($pid) {
        if (isset($pid) && ($pid > 0)) {
            $data["PID"] = $pid;
            $this->load->vars($data);
            $this->load->view('attach_portrait');
        }
    }

    public function save_portrait() {
        $valid_exts = array('jpeg', 'jpg', 'png', 'gif');
        $max_file_size = 300 * 1024; #200kb
        $nw = $nh = 200; # image with # height
        $this->load->model('mpersistent');
        $this->load->helper('form');
        $this->load->helper('directory');
        $data["patient"] = $this->mpersistent->open_id($this->input->post("PID"), "patient", "PID");
        //print_r($data["patient"]);
        //print_r(directory_map('./attach/'.$data["patient"]["HIN"]));
        //print_r($data["patient"]);
        if (!is_dir('./attach/' . $data["patient"]["HIN"])) {
            mkdir('./attach/' . $data["patient"]["HIN"], 0755, TRUE);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['image'])) {
                if (!$_FILES['image']['error'] && $_FILES['image']['size'] < $max_file_size) {
                    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $valid_exts)) {
                        $path = './attach/' . $data["patient"]["HIN"] . '/' . $data["patient"]["HIN"] . '_portrait.jpg'; // . $ext;
                        $size = getimagesize($_FILES['image']['tmp_name']);

                        $x = (int) $this->input->post("x");
                        $y = (int) $this->input->post("y");
                        $w = (int) $_POST['w'] ? $this->input->post("w") : $size[0];
                        $h = (int) $_POST['h'] ? $this->input->post("h") : $size[1];
                        $data = file_get_contents($_FILES['image']['tmp_name']);
                        $vImg = imagecreatefromstring($data);
                        $dstImg = imagecreatetruecolor($nw, $nh);
                        imagecopyresampled($dstImg, $vImg, 0, 0, $x, $y, $nw, $nh, $w, $h);
                        imagejpeg($dstImg, $path);
                        imagedestroy($dstImg);
                        header("Status: 200");
                        header("Location: " . site_url('/patient/view/' . $this->input->post("PID")));
                    } else {
                        echo 'unknown problem!';
                    }
                } else {
                    echo 'file is too small or large';
                }
            } else {
                echo 'file not set';
            }
        } else {
            echo 'bad request!';
        }
    }

    public function save_snap() {
        $valid_exts = array('jpeg', 'jpg', 'png', 'gif');
        $max_file_size = 300 * 1024; #200kb
        $nw = $nh = 200; # image with # height
        $this->load->model('mpersistent');
        $this->load->helper('form');
        $this->load->helper('directory');
        $data["patient"] = $this->mpersistent->open_id($this->input->post("PID"), "patient", "PID");
        //print_r($data["patient"]);
        //print_r(directory_map('./attach/'.$data["patient"]["HIN"]));
        //print_r($data["patient"]);
        if (!is_dir('./attach/' . $data["patient"]["HIN"])) {
            mkdir('./attach/' . $data["patient"]["HIN"], 0755, TRUE);
        }
        $size = getimagesize($_POST['base64image']);

        $x = (int) $this->input->post("x");
        $y = (int) $this->input->post("y");
        $w = (int) $_POST['w'] ? $this->input->post("w") : $size[0];
        $h = (int) $_POST['h'] ? $this->input->post("h") : $size[1];

        $path = './attach/' . $data["patient"]["HIN"] . '/' . $data["patient"]["HIN"] . '_portrait.jpg'; // . $ext;
        $img = $_POST['base64image'];
        $img = str_replace('data:image/jpeg;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $vImg = imagecreatefromstring($data);
        $dstImg = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dstImg, $vImg, 0, 0, $x, $y, $nw, $nh, $w, $h);
        $success = imagejpeg($dstImg, $path);
        imagedestroy($dstImg);
        //print $success ? $dstImg : 'Unable to save the file.';
    }

    public function view($hash) {
        $this->load->model('mpersistent');
        $data["attach"] = $this->mpersistent->open_id($hash, "attachment", "ATTCHID");
        $data["patient"] = $this->mpersistent->open_id($data["attach"]["PID"], "patient", "PID");
        if (isset($data["patient"]["DateOfBirth"])) {
            $data["patient"]["Age"] = Modules::run('patient/get_age', $data["patient"]["DateOfBirth"]);
        }
        $data["attach_comment"] = $this->mpersistent->get_attach_comment($hash);

        $this->load->vars($data);
        $this->load->view('attach_view');
    }

    public function cmnt_save() {
        if (($_POST["ATTID"] > 0 ) && ($_POST["UID"] > 0 )) {

            $data["ATTCHID"] = $_POST["ATTID"];
            $data["Comment_By"] = $_POST["UID"];
            $data["Comment"] = $_POST["comment"];
            $table = 'attachment_comment';

            $this->load->model('mpersistent');

            $id = $this->mpersistent->create($table, $data);
            $ncomment = "[" . date('Y-m-d ') . "] " . $this->session->userdata("FullName") . ":" . $_POST["comment"];
            echo "<div class='comment' id='" . $id . "'>" . $ncomment . "</div>";
        }
    }

    public function save() {

        if (!$_POST) {
            echo "Wrong Data Try again";
            exit;
        }
        if (isset($_POST["PID"])) {
            $this->load->model('mpersistent');
            $data["patient_info"] = $this->mpersistent->open_id($_POST["PID"], "patient", "PID");
        } else {
            echo "Patient not found. try again";
            exit;
        }
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->model("mpersistent");
        $this->form_validation->set_error_delimiters('<span class="field_error">', '</span>');
        $this->form_validation->set_rules("Attach_Type", "Attach_Type", "required");
        if ($this->form_validation->run() == FALSE) {
            $error = array('Attach_Type' => "Please select Attach_Type");
            header("Status: 200");
            header("Location: " . site_url('/form/create/attachment/' . $this->input->post("PID")));
        } else {
            $allowed = array('image/gif', 'image/png', 'image/jpeg', 'application/pdf');
            $mime = mysql_real_escape_string($_FILES['Attach_File']['type']);
            if (!in_array($mime, $allowed)) {
                echo 'File Type Not Allowed';
                return FALSE;
            }

            $file = file_get_contents($_FILES['Attach_File']['tmp_name']); //SQL Injection defence!
            $image_name = mysql_real_escape_string($_FILES['Attach_File']['name']);
            $size = intval($_FILES['Attach_File']['size']);


            $save_data = array(
                "Attach_File" => $file,
                "Attach_Format" => $mime,
                "Attach_Name" => $image_name,
                "Attach_Size" => $size,
                //"Attach_Link" => $config["upload_path"].$data["upload_data"]["file_name"],
                "PID" => $this->input->post("PID"),
                "Attach_Type" => $this->input->post("Attach_Type"),
                //"Attach_To" =>$this->input->post("Attach_To"),
                "Attach_Description" => $this->input->post("Attach_Description")
            );
            $status = $this->mpersistent->create("attachment", $save_data);
            $this->session->set_flashdata(
                    'msg', 'REC: ' . 'File Attached'
            );
        }
        if ($status > 0) {
            header("Status: 200");
            header("Location: " . site_url('patient/view/' . $this->input->post("PID")));
            return;
        }

        /*
          $config = $this->config->item('upload');
          $config["upload_path"] .=  $data["patient_info"]["HIN"].'/';
          if(!is_dir($config["upload_path"])){
          mkdir($config["upload_path"],0755,TRUE);
          }
          $this->load->library('upload',$config);
          $this->load->helper('form');
          $this->load->library('form_validation');
          $this->load->model("mpersistent");
          $this->form_validation->set_error_delimiters('<span class="field_error">', '</span>');
          $this->form_validation->set_rules("Attach_Type", "Attach_Type", "required");
          $this->form_validation->set_rules("Attach_To", "Attach_To", "required");
          if ( ! $this->upload->do_upload("Attach_Name"))
          {
          $error = array('error' => $this->upload->display_errors());
          header("Status: 200");
          header("Location: ".site_url('/form/create/attachment/'.$this->input->post("PID")));
          }
          else
          {
          if ($this->form_validation->run() == FALSE) {
          $error = array('Attach_Type' => "Please select Attach_Type");
          header("Status: 200");
          header("Location: ".site_url('/form/create/attachment/'.$this->input->post("PID")));
          }
          else{
          $data = array('upload_data' => $this->upload->data());
          $save_data = array(
          "Attach_Name" => $data["upload_data"]["orig_name"],
          "Attach_Hash" => md5($data["upload_data"]["raw_name"]),
          "Attach_Link" => $config["upload_path"].$data["upload_data"]["file_name"],
          "PID" =>$this->input->post("PID"),
          "Attach_Type" =>$this->input->post("Attach_Type"),
          "Attach_To" =>$this->input->post("Attach_To"),
          "Attach_Description" =>$this->input->post("Attach_Description")
          );
          $status = $this->mpersistent->create("attachment",$save_data);
          $this->session->set_flashdata(
          'msg', 'REC: ' . 'File Attached'
          );
          if ( $status>0){
          header("Status: 200");
          header("Location: ".site_url('patient/view/'.$this->input->post("PID")));
          return;
          }
          }
          } */
    }

    public function instrument_interface() {

        $config = $this->config->item('upload');
        $config["upload_path"] .= 'csv' . '/';
        if (!is_dir($config["upload_path"])) {
            mkdir($config["upload_path"], 0755, TRUE);
        }
        $this->load->library('upload', $config);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->model("mpersistent");
        $this->form_validation->set_error_delimiters('<span class="field_error">', '</span>');

        $allowed = array('text/csv');
        $mime = mysql_real_escape_string($_FILES['Attach_Name']['type']);
         if (!in_array($mime, $allowed)) {
            echo 'File Type Not Allowed';
            return FALSE;
        }
        
        if (intval($_FILES['Attach_Name']['size'])>10485760) { //Max 10 MB
            echo 'File is too large';
            return FALSE;
        }

       


        if ($this->upload->do_upload("Attach_Name")) {

            $this->session->set_flashdata(
                    'msg', 'REC: ' . 'File Attached'
            );

            $file = $this->upload->data();

            $filename = $file['file_name'];

// Name of your CSV file
            $csv_file = $config["upload_path"] . $filename;


            if (($handle = fopen($csv_file, "r")) !== FALSE) {
                fgetcsv($handle);
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);
                    for ($c = 0; $c < $num; $c++) {
                        $col[$c] = $data[$c];
                    }

                    $col1 = $col[0];
                    $col2 = $col[1];
                    $col3 = $col[2];
                    $col4 = $col[3];
                    $col5 = $col[4];

// SQL Query to insert data into DataBase
                    $save_data = array(
                        "LAB_ORDER_ID" => $col2,
                        "Test_Name" => $col3,
                        "Test_Code" => $col4,
                        "T_Result" => $col5
                    );
                    $status = $this->mpersistent->create("csvtbl", $save_data);
                }
                fclose($handle);

                if ($status > 0) {
                    
                $update_lab_order = $this->mpersistent->update_lab_order();    
                    
                $this->session->set_flashdata(
                        'msg', 'REC: ' . 'Laboratory Result Updated'
                );
                    header("Status: 200");
                    header("Location: " . site_url('search/lab_orders'));
                    return;
                }
            }
        } else {

            $error = array('error' => $this->upload->display_errors());
            header("Status: 200");
            header("Location: " . site_url('/form/create/instrument_interface/'));
        }
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */