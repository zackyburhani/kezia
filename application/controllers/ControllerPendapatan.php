<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ControllerPendapatan extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Model');
		$username = $this->session->username;
		if($username == null){
			redirect('');
		}
	}
	
	function index()
	{	
		$this->load->view('template/v_header');
		$this->load->view('template/v_sidebar');
		$this->load->view('v_pendapatan');
		$this->load->view('template/v_footer');
	}

	public function cetak()
    {
    	$awal = $this->input->post('awal');
    	$akhir = $this->input->post('akhir');

    	if($akhir < $awal){
            $this->session->set_flashdata('pesanGagal','Tanggal Tidak Valid');
    		redirect('laporan_pendapatan');
    	} 

        $pdf = new FPDF('P','mm','A4');
        // membuat halaman baru
        $pdf->AddPage();
        // setting jenis font yang akan digunakan
        $pdf->SetFont('Arial','B',16);
        //cetak gambar
        $image1 = "assets/img/logo.png";
        $pdf->Cell(1, 0, $pdf->Image($image1, $pdf->GetX(), $pdf->GetY(), 44.20), 0, 0, 'L', false );
        // mencetak string
        $pdf->Cell(186,10,'Kezia Laundry Service',0,1,'C');
        $pdf->Cell(9,1,'',0,1);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(186,7,'Jl. Kemanggisan Pulo RT.04 / RW.17 No. 71',0,1,'C');
        $pdf->Cell(186,3,'Palmerah, Jakarta barat',0,1,'C');
        $pdf->Cell(186,5,'',0,1,'C');

        $pdf->Line(10, 42, 210-11, 42); 
        $pdf->SetLineWidth(0.5); 
        $pdf->Line(10, 42, 210-11, 42);
        $pdf->SetLineWidth(0);     
            
        $pdf->ln(6);        
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(10,1,'',0,1);
        $pdf->Cell(190,10,'LAPORAN PENDAPATAN PERIODE '.$awal. ' SAMPAI '.$akhir,0,1,'C');
        
        $pdf->Cell(10,-1,'',0,1);

        $pendapatan = $this->Model->lapPendapatan($awal,$akhir);

        if($pendapatan == null) {
            $this->session->set_flashdata('pesanGagal','Data Tidak Ditemukan');
        	redirect('laporan_pendapatan');
        }

        $pdf->Cell(190,5,' ',0,1,'C');
        // Memberikan space kebawah agar tidak terlalu rapat
        $pdf->Cell(10,1,'',0,1);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(10,6,'No.',1,0,'C');
        $pdf->Cell(40,6,'Kode Order',1,0,'C');
        $pdf->Cell(55,6,'Nama Pelanggan',1,0,'C');
        $pdf->Cell(40,6,'Jasa',1,0,'C');
        $pdf->Cell(45,6,'Jumlah',1,1,'C');
        $pdf->SetFont('Arial','',8);

        $tampung = array();
        $no = 1;
        foreach ($pendapatan as $row)
        {
            $pdf->Cell(10,6,$no++.".",1,0,'C');
            $pdf->Cell(40,6,$row->kd_order,1,0,'C');
            $pdf->Cell(55,6,ucwords($row->nm_pelanggan),1,0);
            $pdf->Cell(40,6,ucwords($row->nm_jasa),1,0,'C');
            $pdf->Cell(45,6,number_format($row->jumlah,0,',','.'),1,1,'C');
            $tampung[] = $row->jumlah;
        }

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(145,6,'Grand Total',1,0,'C');
        $pdf->Cell(45,6,'Rp. '.number_format(array_sum($tampung),0,',','.'),1,1,'C');
        $pdf->SetFont('Arial','',8);

        $fileName = 'LAPORAN_PENDAPATAN_'.$awal.'/'.$akhir.'.pdf';
        $pdf->Output('D',$fileName); 
    }
}
