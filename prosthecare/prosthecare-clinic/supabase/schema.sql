-- Enable UUID Extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Profiles Table
CREATE TABLE IF NOT EXISTS public.profiles (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    clinic_name VARCHAR(200) DEFAULT 'ProstheCare Orthotics & Prosthetics Center',
    role VARCHAR(50) DEFAULT 'Prosthetist',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Patients Table
CREATE TABLE IF NOT EXISTS public.patients (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    patient_code VARCHAR(30) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    dob DATE,
    gender VARCHAR(20),
    phone VARCHAR(50),
    email VARCHAR(150),
    amputation_level VARCHAR(100),
    affected_side VARCHAR(20),
    activity_level VARCHAR(20),
    device_type VARCHAR(150),
    fitting_stage VARCHAR(50) DEFAULT 'Assessment',
    status VARCHAR(50) DEFAULT 'Active',
    clinical_notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Appointments Table
CREATE TABLE IF NOT EXISTS public.appointments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    patient_id UUID REFERENCES public.patients(id) ON DELETE CASCADE NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    appointment_type VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'Scheduled',
    room VARCHAR(50) DEFAULT 'Gait Lab 1',
    practitioner_notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Care Plans Table
CREATE TABLE IF NOT EXISTS public.care_plans (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    patient_id UUID REFERENCES public.patients(id) ON DELETE CASCADE NOT NULL,
    plan_title VARCHAR(200) NOT NULL,
    prescription_code VARCHAR(50),
    socket_type VARCHAR(100),
    suspension_type VARCHAR(100),
    fabrication_stage VARCHAR(50) DEFAULT 'Cast Modification',
    target_delivery_date DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Enable Row Level Security (RLS)
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.patients ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.appointments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.care_plans ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Allow access to profiles" ON public.profiles FOR ALL USING (true) WITH CHECK (true);
CREATE POLICY "Allow access to patients" ON public.patients FOR ALL USING (true) WITH CHECK (true);
CREATE POLICY "Allow access to appointments" ON public.appointments FOR ALL USING (true) WITH CHECK (true);
CREATE POLICY "Allow access to care_plans" ON public.care_plans FOR ALL USING (true) WITH CHECK (true);

-- Seed Sample Data
INSERT INTO public.patients (patient_code, first_name, last_name, dob, gender, phone, email, amputation_level, affected_side, activity_level, device_type, fitting_stage, status)
VALUES
('PT-2026-081', 'Eleanor', 'Vance', '1984-06-15', 'Female', '+1 (555) 234-5678', 'eleanor.vance@example.com', 'Transtibial (Below Knee)', 'Right', 'K3', 'Endoskeletal Carbon Foot with Vacuum Socket', 'Trial Fitting', 'Active'),
('PT-2026-082', 'Marcus', 'Chen', '1991-11-20', 'Male', '+1 (555) 876-5432', 'marcus.chen@example.com', 'Transfemoral (Above Knee)', 'Left', 'K4', 'Genium Microprocessor Knee & Hydraulic Ankle', 'Definitive Lamination', 'In Fabrication')
ON CONFLICT (patient_code) DO NOTHING;