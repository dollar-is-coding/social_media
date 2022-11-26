<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BaiDang;
use App\Models\NguoiDung;
use App\Models\TheLoai;
use App\Models\DanhMuc;
use App\Models\KhuVuc;
use App\Models\HinhAnh;
use App\Models\TheoDoi;
use App\Models\LienHe;
use App\Models\BaoCao;
use App\Models\BinhLuan;
use Illuminate\Support\Facades\Auth;

class BaiDangController extends Controller
{
    public function trang_chu()
    {
        $dsTheLoai=TheLoai::all();
        $dsDanhMuc=DanhMuc::all();
        $dsKhuVuc=KhuVuc::all();
        $dsBaiDang=BaiDang::where('trang_thai',1)->orderBy('updated_at','DESC')->get();
        return view('main_pages.new_feed',['dsTheLoai'=>$dsTheLoai,'dsDanhMuc'=>$dsDanhMuc,'dsKhuVuc'=>$dsKhuVuc,'dsBaiDang'=>$dsBaiDang]);
    }
    public function tim_kiem(Request $request) {
        $dsTheLoai=TheLoai::all();
        $dsDanhMuc=DanhMuc::all();
        $dsKhuVuc=KhuVuc::all();
        $dsBaiDang=BaiDang::where([['trang_thai',1],['tieu_de','like','%'.$request->search.'%']])->orWhere([['trang_thai',1],['noi_dung','like','%'.$request->search.'%']])->orderBy('updated_at','DESC')->get();
        if($request->danh_muc!='Danh mục') {
            $dsBaiDang=BaiDang::where([['trang_thai',1],['danh_muc_id',$request->danh_muc]])->orderBy('updated_at','DESC')->get();
        }
        if($request->the_loai!='Thể loại') {
            $dsBaiDang=BaiDang::where([['trang_thai',1],['the_loai_id',$request->the_loai]])->orderBy('updated_at','DESC')->get();
        }
        if($request->khu_vuc!='Khu vực') {
            $dsBaiDang=BaiDang::where([['trang_thai',1],['khu_vuc_id',$request->khu_vuc]])->orderBy('updated_at','DESC')->get();
        }
        return view('main_pages.new_feed',['dsTheLoai'=>$dsTheLoai,'dsDanhMuc'=>$dsDanhMuc,'dsKhuVuc'=>$dsKhuVuc,'dsBaiDang'=>$dsBaiDang]);
    }
    public function xem_bai_dang($id) {
        $array = ['Tin giả', 'Thông tin sai sự thật', 'Vi phạm quy tắc nhóm', 'Hình ảnh chứa nội dung nhạy cảm', 'Spam', 'Ngôn từ gây thù ghét', 'Vấn đề khác',];
        $user=NguoiDung::where('id',Auth::id())->first();
        $chiTietBaiDang=BaiDang::find($id);
        $dsBinhLuan=BinhLuan::where('bai_dang_id',$id)->orderBy('updated_at','DESC')->get();
        $soLuongHinhAnh=HinhAnh::where('bai_dang_id',$id)->count();
        $lienHe=LienHe::where('bai_dang_id',$id)->first();
        $hinhAnh=HinhAnh::where('bai_dang_id',$id)->get();
        $follow=TheoDoi::where('nguoi_dung_id',Auth::id())->where('bai_dang_id',$id)->first();
        return view('main_pages.detail_post',['baiDang'=>$chiTietBaiDang,'soLuongHA'=>$soLuongHinhAnh,'hinhAnh'=>$hinhAnh,'user'=>$user,'daTheoDoi'=>$follow,'lienHe'=>$lienHe,'array'=>$array,'dsBinhLuan'=>$dsBinhLuan]);
    }
    public function bao_cao($idBaiDang, $noiDungBaoCao) {
        BaoCao::create([
            'nguoi_dung_id'=>Auth::id(),
            'bai_dang_id'=>$idBaiDang,
            'noi_dung'=>$noiDungBaoCao,
        ]);
        return redirect()->route('xem-bai-dang',['id'=>$idBaiDang]);
    }
    public function xl_theo_doi($bai_dang_id) {
        TheoDoi::create([
            'nguoi_dung_id'=>Auth::id(),
            'bai_dang_id'=>$bai_dang_id,
            'trang_thai'=>1
        ]);
        return back();
    }
    public function xl_bo_theo_doi($bai_dang_id) {
        TheoDoi::where('bai_dang_id',$bai_dang_id)->update(['trang_thai'=>0]);
        return back();
    }
    public function xl_theo_doi_lai($bai_dang_id) {
        TheoDoi::where('bai_dang_id',$bai_dang_id)->update(['trang_thai'=>1]);
        return back();
    }
    public function ds_bai_dang($id) {
        $nguoiDung=NguoiDung::where('id',$id)->first();
        $dsBaiDang=BaiDang::where('nguoi_dung_id',$id)->orderBy('trang_thai','DESC')->orderBy('updated_at','DESC')->get();
        return view('main_pages.post_list',['user'=>$nguoiDung,'dsBaiDang'=>$dsBaiDang,'id'=>$id]);
    }
    public function ds_theo_doi($id) {
        $nguoiDung=NguoiDung::where('id',$id)->first();
        $dsTheoDoi=TheoDoi::where('nguoi_dung_id',$id)->where('trang_thai',1)->get();
        return view('main_pages.follow_list',['user'=>$nguoiDung,'id'=>$id,'dsTheoDoi'=>$dsTheoDoi]);
    }
    public function dang_bai() {
        $danhMuc=DanhMuc::all();
        $theLoai=TheLoai::all();
        $khuVuc=KhuVuc::all();
        return view('main_pages.post',['danhMuc'=>$danhMuc,'theLoai'=>$theLoai,'khuVuc'=>$khuVuc]);
    }
    public function xu_ly_dang_bai(Request $request) {
        $user=Auth::id();
        $dangBai=BaiDang::create([
            'nguoi_dung_id'=>$user,
            'the_loai_id'=>$request->the_loai,
            'danh_muc_id'=>$request->danh_muc,
            'khu_vuc_id'=>$request->khu_vuc,
            'tieu_de'=>$request->tieu_de,
            'noi_dung'=>$request->noi_dung,
            'dia_chi'=>$request->dia_chi,
            'trang_thai'=>1,
        ]);
        $hinhAnh=BaiDang::latest()->first();
        $lienHe=LienHe::create([
            'bai_dang_id'=>$hinhAnh->id,
            'dien_thoai'=>$request->dien_thoai,
            'zalo'=>$request->zalo,
            'facebook'=>$request->facebook,
        ]);
        if ($request->has('file')) {
            foreach ($request->file('file') as $img) {
                $filename = $img->getClientOriginalName();
                $img->move(public_path('images/added_images'), $filename);
                HinhAnh::create([
                    'bai_dang_id'=>$hinhAnh->id,
                    'hinh_anh'=>$filename,
                ]);
            }
        }
        return redirect()->route('ds-bai-dang',['id'=>Auth::id()]);
    }
    public function show($id) {
        $chiTietBaiDang =BaiDang::find($id);
        $danhMuc=DanhMuc::all();
        $theLoai=TheLoai::all();
        $khuVuc=KhuVuc::all();
        $lienHe=LienHe::where('bai_dang_id',$id)->first();
        $hinhAnh=HinhAnh::where('bai_dang_id',$id)->get();
        return view('main_pages.edit_post',['baiDang'=>$chiTietBaiDang,'danhMuc'=>$danhMuc,'theLoai'=>$theLoai,'khuVuc'=>$khuVuc,'hinhAnh'=>$hinhAnh,'lienHe'=>$lienHe]);
    }
    public function edit($id,Request $request) {
        $chiTietBaiDang=BaiDang::find($id)->update([
            'the_loai_id'=>$request->the_loai,
            'danh_muc_id'=>$request->danh_muc,
            'khu_vuc_id'=>$request->khu_vuc,
            'tieu_de'=>$request->tieu_de,
            'noi_dung'=>$request->noi_dung,
            'dia_chi'=>$request->dia_chi,
        ]);
        $chiTietLienHe=LienHe::where('bai_dang_id',$id)->update([
            'dien_thoai'=>$request->dien_thoai,
            'zalo'=>$request->zalo,
            'facebook'=>$request->facebook,
        ]);
        return redirect()->route('xem-bai-dang',['id'=>$id]);
    }
    public function destroy($id) {
        $xoaBaiDang=BaiDang::find($id)->delete();
        return redirect()->route('trang-chu');
    }
    public function returned($id) {
        $daTimThay=BaiDang::find($id)->update(['trang_thai'=>0]);
        return redirect()->route('xem-bai-dang',['id'=>$id]);
    }
}
