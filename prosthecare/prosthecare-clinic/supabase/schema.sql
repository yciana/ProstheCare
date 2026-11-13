-- ==============================================================================
-- ProstheCare Clinic Management System - Supabase PostgreSQL Schema
-- Project: gdpzdypxospokczofxlu
-- Run this in the Supabase SQL Editor:
-- https://supabase.com/dashboard/project/gdpzdypxospokczofxlu/sql
-- ==============================================================================

-- 1. Enable UUID Extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- 2. Staff & Client Profiles Table
CREATE TABLE IF NOT EXISTS public.profiles (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    email VARCHAR(255),
    full_name VARCHAR(150) NOT NULL,
    clinic_name VARCHAR(200) DEFAULT 'ProstheCare Orthotics & Prosthetics Center',
    role VARCHAR(50) DEFAULT 'Client',
    phone VARCHAR(50),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 3. Patients Table (Prosthetic & Orthotic Clinic Clients)
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
    fitting_stage VARCHAR(50) DEFAULT 'Assessment',
    status VARCHAR(50) DEFAULT 'Active',
    clinical_notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 4. Appointments Table (Clinical Fitting & Alignment Sessions)
CREATE TABLE IF NOT EXISTS public.appointments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    patient_id UUID REFERENCES public.patients(id) ON DELETE CASCADE NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    appointment_type VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'Scheduled',
    room VARCHAR(50) DEFAULT 'Gait Lab 1',
    practitioner_notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 5. Care Plans & Prosthetic Fabrication Table
CREATE TABLE IF NOT EXISTS public.care_plans (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    patient_id UUID REFERENCES public.patients(id) ON DELETE CASCADE NOT NULL,
    plan_title VARCHAR(200) NOT NULL,
    prescription_code VARCHAR(50),
    socket_type VARCHAR(100),
    suspension_type VARCHAR(100),
    componentry_details TEXT,
    fabrication_stage VARCHAR(50) DEFAULT 'Cast Modification',
    target_delivery_date DATE,
    warranty_months INTEGER DEFAULT 24,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 6. Row Level Security (RLS) Configuration
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.patients ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.appointments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.care_plans ENABLE ROW LEVEL SECURITY;

-- Drop and recreate policies to support re-running schema without errors
DROP POLICY IF EXISTS "Allow public access to profiles" ON public.profiles;
CREATE POLICY "Allow public access to profiles" ON public.profiles FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow public access to patients" ON public.patients;
CREATE POLICY "Allow public access to patients" ON public.patients FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow public access to appointments" ON public.appointments;
CREATE POLICY "Allow public access to appointments" ON public.appointments FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow public access to care_plans" ON public.care_plans;
CREATE POLICY "Allow public access to care_plans" ON public.care_plans FOR ALL USING (true) WITH CHECK (true);

-- 7. Seed Initial Clinic Data
INSERT INTO public.patients (patient_code, first_name, last_name, dob, gender, phone, email, amputation_level, affected_side, activity_level, device_type, fitting_stage, status, clinical_notes)
VALUES
('PC-1048', 'Sarah', 'Mitchell', '1989-03-18', 'Female', '+1 555 014 9822', 'sarah.m@example.com', 'Left transtibial', 'Left', 'K3 - Community ambulator', 'Dynamic Response K3', 'Trial Fitting', 'Active', 'Client reports improved comfort since the last socket adjustment. Continue current liner care routine and monitor pressure at distal tibia.'),
('PT-2026-081', 'Eleanor', 'Vance', '1984-06-15', 'Female', '+1 (555) 234-5678', 'eleanor.vance@example.com', 'Transtibial (Below Knee)', 'Right', 'K3', 'Endoskeletal Carbon Foot with Vacuum Socket', 'Trial Fitting', 'Active', 'Standard socket review.'),
('PT-2026-082', 'Marcus', 'Chen', '1991-11-20', 'Male', '+1 (555) 876-5432', 'marcus.chen@example.com', 'Transfemoral (Above Knee)', 'Left', 'K4', 'Genium Microprocessor Knee & Hydraulic Ankle', 'Definitive Lamination', 'In Fabrication', 'Fabrication in progress.'),
('PT-2026-083', 'Sophia', 'Rodriguez', '2001-03-08', 'Female', '+1 (555) 345-6789', 'sophia.r@example.com', 'Transradial (Below Elbow)', 'Right', 'K3', 'Multi-Articulating Myoelectric Hand', 'Assessment', 'Active', 'New intake evaluation.')
ON CONFLICT (patient_code) DO NOTHING;

-- ==============================================================================
-- 8. Staff Operations Tables
-- ==============================================================================

-- Clinic Queue
CREATE TABLE IF NOT EXISTS public.clinic_queue (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    ticket_number VARCHAR(20) NOT NULL,
    client_name VARCHAR(150) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'Waiting',
    room VARCHAR(50),
    estimated_wait_minutes INTEGER DEFAULT 15,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Pending Approvals
CREATE TABLE IF NOT EXISTS public.pending_approvals (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    request_code VARCHAR(50) NOT NULL,
    client_name VARCHAR(150) NOT NULL,
    title VARCHAR(200) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    urgency VARCHAR(50) DEFAULT 'Normal',
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Maintenance Records
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
    pylon_adapters_action TEXT DEFAULT 'Retorqued to spec',
    foot_module_condition VARCHAR(50) DEFAULT 'Good',
    foot_module_action TEXT,
    cosmetic_cover_condition VARCHAR(50) DEFAULT 'Good',
    cosmetic_cover_action TEXT,
    service_notes TEXT,
    is_safe_for_use BOOLEAN DEFAULT true,
    next_service_due DATE,
    status VARCHAR(50) DEFAULT 'Completed',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Announcements
CREATE TABLE IF NOT EXISTS public.announcements (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'Clinic update',
    audience VARCHAR(100) DEFAULT 'Everyone',
    in_app_notification BOOLEAN DEFAULT true,
    email_notification BOOLEAN DEFAULT true,
    sms_notification BOOLEAN DEFAULT false,
    is_scheduled BOOLEAN DEFAULT false,
    scheduled_at TIMESTAMP WITH TIME ZONE,
    status VARCHAR(50) DEFAULT 'Published',
    created_by VARCHAR(150) DEFAULT 'ProstheCare Clinic',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

ALTER TABLE public.clinic_queue ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.pending_approvals ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.maintenance_records ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.announcements ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Allow public access to clinic_queue" ON public.clinic_queue;
CREATE POLICY "Allow public access to clinic_queue" ON public.clinic_queue FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow public access to pending_approvals" ON public.pending_approvals;
CREATE POLICY "Allow public access to pending_approvals" ON public.pending_approvals FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow public access to maintenance_records" ON public.maintenance_records;
CREATE POLICY "Allow public access to maintenance_records" ON public.maintenance_records FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow public access to announcements" ON public.announcements;
CREATE POLICY "Allow public access to announcements" ON public.announcements FOR ALL USING (true) WITH CHECK (true);

INSERT INTO public.clinic_queue (ticket_number, client_name, service_type, status, room, estimated_wait_minutes)
VALUES
('A-04', 'Daniel Lee', 'Prosthetic fitting', 'In service', 'Fitting Room 1', 0),
('A-05', 'Maria Santos', 'Consultation', 'Waiting', 'Consultation 2', 12),
('A-06', 'Robert Chen', 'Maintenance', 'Waiting', 'Gait Lab', 18),
('A-07', 'Sarah Mitchell', 'Prosthetic fitting', 'Checked in', 'Fitting Room 2', 25)
ON CONFLICT DO NOTHING;

INSERT INTO public.pending_approvals (request_code, client_name, title, status)
VALUES
('PR-0192', 'Michael Grant', 'Socket replacement', 'Pending'),
('PR-0190', 'Emma Davis', 'Knee unit upgrade', 'Pending')
ON CONFLICT DO NOTHING;

INSERT INTO public.announcements (title, message, category, audience, in_app_notification, email_notification, sms_notification, status)
VALUES
('Holiday hours this month', 'We''ll be closed on May 26 for the public holiday.', 'Clinic update', 'Everyone', true, true, false, 'Published'),
('Summer prosthetic care', 'Simple ways to protect your liner in warmer weather.', 'Care tip', 'Clients only', true, true, false, 'Published')
ON CONFLICT DO NOTHING;
