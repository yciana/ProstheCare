-- ==============================================================================
-- ProstheCare Clinic Management System - Supabase PostgreSQL Schema
-- ==============================================================================
-- Run this script in the Supabase SQL Editor (Dashboard > SQL Editor > New query)
-- ==============================================================================

-- 1. Enable UUID Extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 2. Staff Profiles Table (extends Supabase auth.users or functions independently)
CREATE TABLE IF NOT EXISTS public.profiles (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    clinic_name VARCHAR(200) DEFAULT 'ProstheCare Orthotics & Prosthetics Center',
    role VARCHAR(50) DEFAULT 'Prosthetist', -- Prosthetist, Orthotist, Clinic Admin, Technician
    phone VARCHAR(50),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 3. Patients Table (Prosthetic & Orthotic Clinic Clients)
CREATE TABLE IF NOT EXISTS public.patients (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    patient_code VARCHAR(30) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    dob DATE,
    gender VARCHAR(20),
    phone VARCHAR(50),
    email VARCHAR(150),
    address TEXT,
    amputation_level VARCHAR(100), -- Transtibial (Below Knee), Transfemoral (Above Knee), Transradial, Transhumeral, Symes, etc.
    affected_side VARCHAR(20),     -- Left, Right, Bilateral
    activity_level VARCHAR(20),    -- K1, K2, K3, K4
    device_type VARCHAR(150),      -- e.g., Carbon Dynamic Foot Prosthesis, C-Leg Microprocessor, Myoelectric Arm
    fitting_stage VARCHAR(50) DEFAULT 'Assessment', -- Assessment, Diagnostic Socket, Trial Fitting, Definitive Lamination, Final Delivery, Follow-up
    status VARCHAR(50) DEFAULT 'Active',            -- Active, In Fabrication, Maintenance, Completed
    clinical_notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 4. Appointments Table (Clinical Fitting & Alignment Sessions)
CREATE TABLE IF NOT EXISTS public.appointments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    patient_id UUID REFERENCES public.patients(id) ON DELETE CASCADE NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    appointment_type VARCHAR(100) NOT NULL, -- Initial Consultation, Diagnostic Socket Fitting, Dynamic Alignment, Component Adjustment, Delivery
    status VARCHAR(50) DEFAULT 'Scheduled',  -- Scheduled, In Progress, Completed, Rescheduled, Cancelled
    room VARCHAR(50) DEFAULT 'Gait Lab 1',
    practitioner_notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- 5. Care Plans & Prosthetic Fabrication Table
CREATE TABLE IF NOT EXISTS public.care_plans (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    patient_id UUID REFERENCES public.patients(id) ON DELETE CASCADE NOT NULL,
    plan_title VARCHAR(200) NOT NULL,
    prescription_code VARCHAR(50),
    socket_type VARCHAR(100),       -- TSB (Total Surface Bearing), IC (Ischial Containment), Sub-Ischial, Myo-Socket
    suspension_type VARCHAR(100),   -- Pin Lock / Shuttle, Suction with Seal, Elevated Vacuum, Anatomical
    componentry_details TEXT,       -- Pylon, Hydraulic Ankle, Microprocessor Knee, Carbon Foot
    fabrication_stage VARCHAR(50) DEFAULT 'Cast Modification', -- Cast Modification, Check Socket, Test Walk, Carbon Lamination, Finished
    target_delivery_date DATE,
    warranty_months INTEGER DEFAULT 24,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- ==============================================================================
-- 6. Row Level Security (RLS) Configuration
-- ==============================================================================
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.patients ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.appointments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.care_plans ENABLE ROW LEVEL SECURITY;

-- Allow read and write for authenticated users and anon keys (adjust for production security)
DROP POLICY IF EXISTS "Allow all access to profiles for authenticated and anon" ON public.profiles;
CREATE POLICY "Allow all access to profiles for authenticated and anon" ON public.profiles FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow all access to patients" ON public.patients;
CREATE POLICY "Allow all access to patients" ON public.patients FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow all access to appointments" ON public.appointments;
CREATE POLICY "Allow all access to appointments" ON public.appointments FOR ALL USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "Allow all access to care_plans" ON public.care_plans;
CREATE POLICY "Allow all access to care_plans" ON public.care_plans FOR ALL USING (true) WITH CHECK (true);

-- ==============================================================================
-- 7. Seed Sample Clinic Data
-- ==============================================================================

-- Sample Patients
INSERT INTO public.patients (patient_code, first_name, last_name, dob, gender, phone, email, amputation_level, affected_side, activity_level, device_type, fitting_stage, status, clinical_notes)
VALUES
('PT-2026-081', 'Eleanor', 'Vance', '1984-06-15', 'Female', '+1 (555) 234-5678', 'eleanor.vance@example.com', 'Transtibial (Below Knee)', 'Right', 'K3', 'Endoskeletal Carbon Foot with Vacuum Socket', 'Trial Fitting', 'Active', 'High mobility user. Evaluated elevated vacuum system for socket stability during trail walking.'),
('PT-2026-082', 'Marcus', 'Chen', '1991-11-20', 'Male', '+1 (555) 876-5432', 'marcus.chen@example.com', 'Transfemoral (Above Knee)', 'Left', 'K4', 'Genium Microprocessor Knee & Hydraulic Ankle', 'Definitive Lamination', 'In Fabrication', 'Athlete looking for high dynamic energy return. Final carbon fiber layup scheduled for Friday.'),
('PT-2026-083', 'Sophia', 'Rodriguez', '2001-03-08', 'Female', '+1 (555) 345-6789', 'sophia.r@example.com', 'Transradial (Below Elbow)', 'Right', 'K3', 'Multi-Articulating Myoelectric Hand', 'Assessment', 'Active', 'Undergoing initial EMG sensor calibration and electrode socket placement test.'),
('PT-2026-084', 'David', 'Kowalski', '1975-09-12', 'Male', '+1 (555) 901-2345', 'david.k@example.com', 'Transtibial (Below Knee)', 'Left', 'K2', 'Energy Storage and Return (ESR) Prosthesis', 'Final Delivery', 'Completed', 'Definitive device delivered. Gait is symmetrical. Scheduled routine 30-day review.'),
('PT-2026-085', 'Amara', 'Okonkwo', '1996-07-28', 'Female', '+1 (555) 432-1098', 'amara.o@example.com', 'Bilateral Transtibial', 'Bilateral', 'K3', 'Dual All-Terrain Shock-Absorbing Pylons', 'Diagnostic Socket', 'Active', 'First test walk with transparent check sockets. Slight pressure medial distal right tibia noted and relieved.')
ON CONFLICT (patient_code) DO NOTHING;

-- Sample Appointments
INSERT INTO public.appointments (patient_id, appointment_date, appointment_time, appointment_type, status, room, practitioner_notes)
SELECT id, CURRENT_DATE, '09:30:00', 'Trial Fitting & Dynamic Alignment', 'Scheduled', 'Gait Analysis Lab A', 'Evaluate stride symmetry on force plate treadmill.'
FROM public.patients WHERE patient_code = 'PT-2026-081' LIMIT 1;

INSERT INTO public.appointments (patient_id, appointment_date, appointment_time, appointment_type, status, room, practitioner_notes)
SELECT id, CURRENT_DATE, '11:00:00', 'Diagnostic Socket Evaluation', 'Scheduled', 'Fitting Suite 2', 'Adjust distal clearance and suction sleeve fit.'
FROM public.patients WHERE patient_code = 'PT-2026-085' LIMIT 1;

INSERT INTO public.appointments (patient_id, appointment_date, appointment_time, appointment_type, status, room, practitioner_notes)
SELECT id, CURRENT_DATE + INTERVAL '1 day', '14:00:00', 'Myoelectric Electrode Tuning', 'Scheduled', 'Upper Extremity Lab', 'Fine-tune dual-site EMG thresholds for grip pattern switching.'
FROM public.patients WHERE patient_code = 'PT-2026-083' LIMIT 1;

INSERT INTO public.appointments (patient_id, appointment_date, appointment_time, appointment_type, status, room, practitioner_notes)
SELECT id, CURRENT_DATE + INTERVAL '2 day', '10:15:00', 'Final Delivery & Gait Training', 'Scheduled', 'Gait Analysis Lab B', 'Handover definitive prosthesis, provide maintenance guide and wear schedule.'
FROM public.patients WHERE patient_code = 'PT-2026-082' LIMIT 1;

-- Sample Care Plans
INSERT INTO public.care_plans (patient_id, plan_title, prescription_code, socket_type, suspension_type, componentry_details, fabrication_stage, target_delivery_date, warranty_months, notes)
SELECT id, 'High-Performance Transtibial System', 'RX-PRO-4402', 'TSB (Total Surface Bearing)', 'Elevated Vacuum with Hypobaric Ring', 'Triton Carbon Foot + Harmony P3 vacuum pump', 'Check Socket', CURRENT_DATE + INTERVAL '14 day', 24, 'Patient aims for trail hiking. Socket trim lines lowered for maximum knee flexion.'
FROM public.patients WHERE patient_code = 'PT-2026-081' LIMIT 1;

INSERT INTO public.care_plans (patient_id, plan_title, prescription_code, socket_type, suspension_type, componentry_details, fabrication_stage, target_delivery_date, warranty_months, notes)
SELECT id, 'Microprocessor Transfemoral Dynamic System', 'RX-PRO-9921', 'IC (Ischial Containment)', 'Sub-atmospheric Active Suction', 'Ottobock Genium + Taleo Carbon Foot', 'Carbon Lamination', CURRENT_DATE + INTERVAL '5 day', 36, 'Multi-layer prepreg carbon structure with titanium pyramid adapters.'
FROM public.patients WHERE patient_code = 'PT-2026-082' LIMIT 1;
