-- ==============================================================================
-- ProstheCare Clinic Management System - Staff Schema & Tables
-- Run this script in your Supabase SQL Editor:
-- https://supabase.com/dashboard/project/gdpzdypxospokczofxlu/sql
-- ==============================================================================

-- 1. Patients Table Update & Seed (Client records for Screenshot 2)
CREATE TABLE IF NOT EXISTS public.patients (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    patient_code VARCHAR(30) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    dob DATE,
    gender VARCHAR(20),
    phone VARCHAR(50),
    email VARCHAR(150),
    amputation_level VARCHAR(100),
    affected_side VARCHAR(20),
    activity_level VARCHAR(50),
    device_type VARCHAR(150),
    fitting_stage VARCHAR(50) DEFAULT 'Trial Fitting',
    status VARCHAR(50) DEFAULT 'Active',
    clinical_notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 2. Clinic Queue Table (Live operations queue on Staff Dashboard - Screenshot 1)
CREATE TABLE IF NOT EXISTS public.clinic_queue (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    ticket_number VARCHAR(20) NOT NULL,
    client_name VARCHAR(150) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'Waiting', -- 'In service', 'Waiting', 'Checked in', 'Completed'
    room VARCHAR(50),
    estimated_wait_minutes INTEGER DEFAULT 15,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 3. Pending Approvals Table (Prosthetic requests needing staff review - Screenshot 1)
CREATE TABLE IF NOT EXISTS public.pending_approvals (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    request_code VARCHAR(50) NOT NULL,
    client_name VARCHAR(150) NOT NULL,
    title VARCHAR(200) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending', -- 'Pending', 'Approved', 'Rejected'
    urgency VARCHAR(50) DEFAULT 'Normal',
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 4. Maintenance Records Table (Clinical service & inspection - Screenshot 3)
CREATE TABLE IF NOT EXISTS public.maintenance_records (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    client_name VARCHAR(150) NOT NULL,
    client_code VARCHAR(50) DEFAULT 'PC-1048',
    device_serial VARCHAR(100) DEFAULT 'TT-2023-8841',
    device_type VARCHAR(150) DEFAULT 'Transtibial - Left',
    technician_name VARCHAR(150) DEFAULT 'Alex Morgan',
    service_date DATE NOT NULL,
    service_type VARCHAR(100) DEFAULT 'Routine maintenance',
    socket_liner_condition VARCHAR(50) DEFAULT 'Good',
    socket_liner_action TEXT,
    pylon_adapters_condition VARCHAR(50) DEFAULT 'Monitor',
    pylon_adapters_action TEXT DEFAULT 'retorqued to spec',
    foot_module_condition VARCHAR(50) DEFAULT 'Good',
    foot_module_action TEXT,
    cosmetic_cover_condition VARCHAR(50) DEFAULT 'Good',
    cosmetic_cover_action TEXT,
    service_notes TEXT,
    is_safe_for_use BOOLEAN DEFAULT true,
    next_service_due DATE,
    status VARCHAR(50) DEFAULT 'Completed', -- 'Draft', 'Completed'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 5. Clinic Announcements Table (Clinic-wide updates & live preview - Screenshot 4)
CREATE TABLE IF NOT EXISTS public.announcements (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'Clinic update',
    audience VARCHAR(100) DEFAULT 'Everyone', -- 'Everyone', 'Clients only', 'Staff only'
    in_app_notification BOOLEAN DEFAULT true,
    email_notification BOOLEAN DEFAULT true,
    sms_notification BOOLEAN DEFAULT false,
    is_scheduled BOOLEAN DEFAULT false,
    scheduled_at TIMESTAMP WITH TIME ZONE,
    status VARCHAR(50) DEFAULT 'Published', -- 'Draft', 'Published', 'Scheduled'
    created_by VARCHAR(150) DEFAULT 'ProstheCare Clinic',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 6. Enable Row Level Security (RLS)
ALTER TABLE public.patients ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.clinic_queue ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.pending_approvals ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.maintenance_records ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.announcements ENABLE ROW LEVEL SECURITY;

-- 7. Public Access Policies for API client
DROP POLICY IF EXISTS "Allow public access to patients" ON public.patients;
CREATE POLICY "Allow public access to patients" ON public.patients FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow public access to clinic_queue" ON public.clinic_queue;
CREATE POLICY "Allow public access to clinic_queue" ON public.clinic_queue FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow public access to pending_approvals" ON public.pending_approvals;
CREATE POLICY "Allow public access to pending_approvals" ON public.pending_approvals FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow public access to maintenance_records" ON public.maintenance_records;
CREATE POLICY "Allow public access to maintenance_records" ON public.maintenance_records FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow public access to announcements" ON public.announcements;
CREATE POLICY "Allow public access to announcements" ON public.announcements FOR ALL USING (true) WITH CHECK (true);

-- 8. Seed Initial Operational Data Matching Figma Screenshots
-- Seed Sarah Mitchell Client Record (Screenshot 2)
INSERT INTO public.patients (patient_code, first_name, last_name, dob, gender, phone, email, amputation_level, affected_side, activity_level, device_type, fitting_stage, status, clinical_notes)
VALUES
('PC-1048', 'Sarah', 'Mitchell', '1989-03-18', 'Female', '+1 555 014 9822', 'sarah.m@example.com', 'Left transtibial', 'Left', 'K3 - Community ambulator', 'Dynamic Response K3', 'Trial Fitting', 'Active', 'Client reports improved comfort since the last socket adjustment. Continue current liner care routine and monitor pressure at distal tibia.')
ON CONFLICT (patient_code) DO UPDATE SET
    first_name = EXCLUDED.first_name,
    last_name = EXCLUDED.last_name,
    amputation_level = EXCLUDED.amputation_level,
    device_type = EXCLUDED.device_type,
    activity_level = EXCLUDED.activity_level,
    clinical_notes = EXCLUDED.clinical_notes;

-- Seed Live Clinic Queue (Screenshot 1)
INSERT INTO public.clinic_queue (ticket_number, client_name, service_type, status, room, estimated_wait_minutes)
VALUES
('A-04', 'Daniel Lee', 'Prosthetic fitting', 'In service', 'Fitting Room 1', 0),
('A-05', 'Maria Santos', 'Consultation', 'Waiting', 'Consultation 2', 12),
('A-06', 'Robert Chen', 'Maintenance', 'Waiting', 'Gait Lab', 18),
('A-07', 'Sarah Mitchell', 'Prosthetic fitting', 'Checked in', 'Fitting Room 2', 25)
ON CONFLICT DO NOTHING;

-- Seed Pending Approvals (Screenshot 1)
INSERT INTO public.pending_approvals (request_code, client_name, title, status)
VALUES
('PR-0192', 'Michael Grant', 'Socket replacement', 'Pending'),
('PR-0190', 'Emma Davis', 'Knee unit upgrade', 'Pending')
ON CONFLICT DO NOTHING;

-- Seed Initial Maintenance Record (Screenshot 3)
INSERT INTO public.maintenance_records (
    client_name, client_code, device_serial, device_type, technician_name,
    service_date, service_type, socket_liner_condition, socket_liner_action,
    pylon_adapters_condition, pylon_adapters_action, foot_module_condition,
    foot_module_action, cosmetic_cover_condition, cosmetic_cover_action,
    service_notes, is_safe_for_use, next_service_due, status
)
VALUES (
    'Sarah Mitchell', 'PC-1048', 'TT-2023-8841', 'Transtibial - Left', 'Alex Morgan',
    '2025-05-14', 'Routine maintenance', 'Good', '',
    'Monitor', 'retorqued to spec', 'Good', '',
    'Good', '',
    'Completed full device inspection. Cleaned and checked all fasteners. Pylon adapter retorqued to manufacturer specification. Gait alignment remains satisfactory.',
    true, '2025-11-14', 'Completed'
)
ON CONFLICT DO NOTHING;

-- Seed Announcements (Screenshot 4)
INSERT INTO public.announcements (title, message, category, audience, in_app_notification, email_notification, sms_notification, status)
VALUES
('Holiday hours this month', 'We''ll be closed on May 26 for the public holiday.', 'Clinic update', 'Everyone', true, true, false, 'Published'),
('Summer prosthetic care', 'Simple ways to protect your liner in warmer weather.', 'Care tip', 'Clients only', true, true, false, 'Published')
ON CONFLICT DO NOTHING;
