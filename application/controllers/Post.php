<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Post extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mod_login');
        $this->load->model('Mod_userlevel');
        $this->load->model('Mod_socialmedia');
        $this->load->model('Mod_banner');
        $this->load->model('Mod_tentang');
        $this->load->model('Mod_bijiarabika');
        $this->load->model('Mod_produk');
        $this->load->model('Mod_testimonial');
        $this->load->model('Mod_artikel');
    }

    public function index()
    {

    }

    public function data($i)
    {
        $data['socialmedia'] = $this->Mod_socialmedia->get_all();
        $data['artikel'] = $this->Mod_artikel->get_data($i);

        $this->load->view('frontend/post', $data);
    }

    function login()
    {

        $this->_validate();
        //cek username database
        $username = anti_injection($this->input->post('username'));
        $status = $this->Mod_login->check_status($username);

        if ($this->Mod_login->check_db($username)->num_rows() == 1) {
            if ($status->is_active != 'N') {
                $db = $this->Mod_login->check_db($username)->row();
                $apl = $this->Mod_login->Aplikasi()->row();

                if (hash_verified(anti_injection($this->input->post('password')), $db->password)) {
                    //cek username dan password yg ada di database
                    $userdata = array(
                        'id_user'  => $db->id_user,
                        'username'    => ucfirst($db->username),
                        'user_name'    => $db->username,
                        'full_name'   => ucfirst($db->full_name),
                        'password'    => $db->password,
                        'id_level'    => $db->id_level,
                        'aplikasi'    => $apl->nama_aplikasi,
                        'title'       => $apl->title,
                        'logo'        => $apl->logo,
                        'nama_owner'     => $apl->nama_owner,
                        'image'       => $db->image,
                        'logged_in'    => TRUE,
                        'hak_akses' => ''
                    );

                    $checklevel = $this->_cek_status($userdata['id_level']);

                    if ($checklevel == 'Admin') {
                        $data['url'] = 'dashboard';
                        $userdata['hak_akses'] = 'Admin';
                    } else if ($checklevel == 'Guest') {
                        $data['url'] = 'dashboard';
                        $userdata['hak_akses'] = 'Guest';
                    }

                    $this->session->set_userdata($userdata);
                    $data['status'] = TRUE;
                    echo json_encode($data);
                } else {

                    $data['pesan'] = "Username atau Password Salah!";
                    $data['error'] = TRUE;
                    echo json_encode($data);
                }
            } else {
                $data['pesan'] = "Akun Anda belum aktif, silakan hubungi Administrator";
                $data['error'] = TRUE;
                echo json_encode($data);
            }
        } else {
            $data['pesan'] = "Username atau Password belum terdaftar!";
            $data['error'] = TRUE;
            echo json_encode($data);
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        $this->load->driver('cache');
        $this->cache->clean();
        ob_clean();
        redirect('login');
    }

    private function _validate()
    {
        $data = array();
        $data['error_string'] = array();
        $data['inputerror'] = array();
        $data['status'] = TRUE;

        if ($this->input->post('username') == '') {
            $data['inputerror'][] = 'username';
            $data['error_string'][] = 'Username is required';
            $data['status'] = FALSE;
        }

        if ($this->input->post('password') == '') {
            $data['inputerror'][] = 'password';
            $data['error_string'][] = 'Password is required';
            $data['status'] = FALSE;
        }

        if ($data['status'] === FALSE) {
            echo json_encode($data);
            exit();
        }
    }

    private function _cek_status($id_level)
    {
        $nama_level = $this->Mod_userlevel->getUserlevel($id_level);
        return $nama_level->nama_level;
    }
}

/* End of file Post.php */
