-- ============================================================
-- POSUNG HRIS - Loại bỏ trường Đảng, Đoàn, LLVT
-- ============================================================

ALTER TABLE `employees`
  DROP COLUMN IF EXISTS `party_join_date`,
  DROP COLUMN IF EXISTS `ly_luan_chinh_tri`,
  DROP COLUMN IF EXISTS `quan_ly_nha_nuoc`,
  DROP COLUMN IF EXISTS `quan_ly_kinh_te`,
  DROP COLUMN IF EXISTS `bd_an_ninh_quoc_phong`,
  DROP COLUMN IF EXISTS `ngay_vao_doan`,
  DROP COLUMN IF EXISTS `chuc_vu_doan`,
  DROP COLUMN IF EXISTS `ngay_chinh_thuc_dang`,
  DROP COLUMN IF EXISTS `chuc_vu_dang`,
  DROP COLUMN IF EXISTS `noi_ket_nap_dang`,
  DROP COLUMN IF EXISTS `ngay_nhap_ngu`,
  DROP COLUMN IF EXISTS `ngay_xuat_ngu`,
  DROP COLUMN IF EXISTS `quan_ham_cao_nhat`,
  DROP COLUMN IF EXISTS `chuc_vu_llvt_cao_nhat`,
  DROP COLUMN IF EXISTS `danh_hieu_cao_nhat`,
  DROP COLUMN IF EXISTS `nam_phong_danh_hieu`;
