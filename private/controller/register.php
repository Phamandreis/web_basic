<?php
class Register extends Controller{
    function sayhi(){
        // khởi tạo đối tượng tèo sử dụng được những hàm từ đối tượng sinhvienmodel
        $teo = $this->model('SinhVienModel');

        // khời tạo đối tượng sử dụng view của home load ra data là danh sách student 

        $dssv = $teo->SinhVien();
        $this->view('Register',[
            'SV'=> $dssv,
            "number" =>'',
            "mau"=>"red"
        ]);
    }
    
}