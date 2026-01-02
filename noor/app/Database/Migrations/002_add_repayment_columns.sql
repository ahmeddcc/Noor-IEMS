-- Migration: Add repayment tracking columns to transactions table
-- Date: 2025-12-31
-- Description: إضافة أعمدة لتتبع سداد السلف

-- إضافة عمود لتمييز المعاملات كسداد
ALTER TABLE transactions ADD COLUMN is_repayment INTEGER DEFAULT 0;

-- إضافة عمود للربط بالسلفة الأصلية (اختياري)
ALTER TABLE transactions ADD COLUMN original_advance_id INTEGER;
