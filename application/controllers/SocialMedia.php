<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Socialmedia extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mod_socialmedia');
    }

    public function index()
    {
        $data['judul'] = 'Social Media';

        $data['modal_edit'] = show_my_modal('socialmedia/modal_edit_social_media', $data);

        $logged_in = $this->session->userdata('logged_in');
        if ($logged_in != TRUE || empty($logged_in)) {
            redirect('login');
        } else {
            $checklevel = $this->session->userdata('hak_akses');

            if ($checklevel == 'Guest') {
                $js = $this->load->view('cluster/cluster-guest-js', null, true);
                $this->template->views('cluster/home-guest', $data, $js);
            } else {
                $js = $this->load->view('socialmedia/socialmedia-js', null, true);
                $this->template->views('socialmedia/home', $data, $js);
            }
        }
    }

    public function ajax_list()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(3600);
        $list = $this->Mod_socialmedia->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $soc) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $soc->nama;
            $row[] = $soc->id;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Mod_socialmedia->count_all(),
            "recordsFiltered" => $this->Mod_socialmedia->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function edit($id)
    {

        $data = $this->Mod_socialmedia->get_data($id);
        echo json_encode($data);
    }

    public function insert()
    {
        $this->_validate();

        $post = $this->input->post();

        $this->nama_cluster = $post['nama_cluster'];

        $this->Mod_cluster->insert($this);
        echo json_encode(array("status" => TRUE));
    }

    public function update()
    {
        $this->_validate();
        $id      = $this->input->post('id');
        $post = $this->input->post();

        $this->nama = $post['nama'];
        $this->link = $post['link'];
        $this->icon = $post['icon'];

        $this->Mod_socialmedia->update($id, $this);
        echo json_encode(array("status" => TRUE));
    }

    public function delete()
    {
        $id = $this->input->post('id_cluster');

        $this->Mod_cluster->delete($id);
        echo json_encode(array("status" => TRUE));
    }

    private function _validate()
    {
        $data = array();
        $data['error_string'] = array();
        $data['inputerror'] = array();
        $data['status'] = TRUE;

        if ($this->input->post('link') == '') {
            $data['inputerror'][] = 'link';
            $data['error_string'][] = 'Link URL Tidak Boleh Kosong';
            $data['status'] = FALSE;
        }

        if ($this->input->post('icon') == '') {
            $data['inputerror'][] = 'icon';
            $data['error_string'][] = 'Icon Tidak Boleh Kosong';
            $data['status'] = FALSE;
        }

        if ($data['status'] === FALSE) {
            echo json_encode($data);
            exit();
        }
    }
}

/* End of file Cluster.php */