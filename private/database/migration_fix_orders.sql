-- Migration: Fix orders table for checkout flow
-- Date: 2026-04-15
-- Description: 
--   1. Add missing 'shipping_agency' column
--   2. Expand 'payment_status' ENUM to include all MercadoPago statuses

-- 1. Add shipping_agency column
ALTER TABLE `orders` 
ADD COLUMN `shipping_agency` varchar(100) DEFAULT NULL 
AFTER `preference_id`;

-- 2. Expand payment_status ENUM to match MercadoPago statuses + our internal 'completed'
ALTER TABLE `orders` 
MODIFY COLUMN `payment_status` enum('pending','paid','failed','approved','rejected','cancelled','refunded','in_process','completed') DEFAULT 'pending';
