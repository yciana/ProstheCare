-- ==============================================================================
-- ProstheCare Clinic Management System - Admin Schema & Seed Data
-- Run this in your Supabase SQL Editor:
-- https://supabase.com/dashboard/project/gdpzdypxospokczofxlu/sql
-- ==============================================================================

-- 1. Prosthetist Availability Table (Screenshot 3)
CREATE TABLE IF NOT EXISTS public.prosthetist_availability (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    practitioner_name VARCHAR(150) NOT NULL,
    title VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'Available', -- 'Available', 'In session', 'Offsite'
    available_hours VARCHAR(100),
    avatar_initials VARCHAR(10),
    display_order INTEGER DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Seed initial prosthetists matching Screenshot 3
INSERT INTO public.prosthetist_availability (practitioner_name, title, status, available_hours, avatar_initials, display_order)
VALUES 
    ('Dr. James Wilson', 'Prosthetist', 'Available', '2:00 PM – 5:00 PM', 'JW', 1),
    ('Dr. Rachel Kim', 'Senior Prosthetist', 'In session', 'Available after 2:30 PM', 'RK', 2),
    ('David Morgan', 'Prosthetic Technician', 'Offsite', 'Returns tomorrow', 'DM', 3),
    ('Amelia Patel', 'Prosthetist', 'Available', 'Until 4:30 PM', 'AP', 4)
ON CONFLICT DO NOTHING;

-- 2. Reference Data Table (Services, Components, Appointment Types - Screenshot 6)
CREATE TABLE IF NOT EXISTS public.reference_data (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    category VARCHAR(50) NOT NULL, -- 'Services', 'Components', 'Appointment types'
    item_code VARCHAR(20),
    name VARCHAR(200) NOT NULL,
    duration_text VARCHAR(50),
    status VARCHAR(50) DEFAULT 'Active',
    display_order INTEGER DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

-- Seed reference data matching Screenshot 6
INSERT INTO public.reference_data (category, item_code, name, duration_text, status, display_order)
VALUES
    ('Services', '01', 'Initial consultation', '60 min', 'Active', 1),
    ('Services', '02', 'Prosthetic fitting', '45 min', 'Active', 2),
    ('Services', '03', 'Maintenance & repair', '30 min', 'Active', 3),
    ('Services', '04', 'Gait assessment', '45 min', 'Active', 4),
    ('Components', '01', 'Dynamic Response Foot Module', 'Catalog', 'Active', 1),
    ('Components', '02', 'Polycentric Pneumatic Knee', 'Catalog', 'Active', 2),
    ('Components', '03', 'Total Surface Bearing Socket', 'Catalog', 'Active', 3),
    ('Appointment types', '01', 'Consultation', '60 min', 'Active', 1),
    ('Appointment types', '02', 'Device Fitting', '45 min', 'Active', 2),
    ('Appointment types', '03', 'Repair & Tuning', '30 min', 'Active', 3)
ON CONFLICT DO NOTHING;

-- 3. Audit Logs / Recent Activity Table (Screenshot 1)
CREATE TABLE IF NOT EXISTS public.audit_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    event_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    actor_name VARCHAR(150) NOT NULL,
    time_label VARCHAR(50) DEFAULT 'Just now',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc'::text, NOW()) NOT NULL
);

INSERT INTO public.audit_logs (event_type, description, actor_name, time_label)
VALUES
    ('New client registered', 'Olivia Parker registered online', 'Olivia Parker', '2 min ago'),
    ('Request approved', 'Request PR-0192 approved', 'Dr. James Wilson', '18 min ago'),
    ('Reference data updated', 'Services reference catalog updated', 'Admin User', '1 hr ago')
ON CONFLICT DO NOTHING;

-- 4. Enable Row Level Security (RLS) & Public Policies
ALTER TABLE public.prosthetist_availability ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.reference_data ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.audit_logs ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Allow public read on prosthetist_availability" 
ON public.prosthetist_availability FOR SELECT USING (true);

CREATE POLICY "Allow public all on prosthetist_availability" 
ON public.prosthetist_availability FOR ALL USING (true);

CREATE POLICY "Allow public read on reference_data" 
ON public.reference_data FOR SELECT USING (true);

CREATE POLICY "Allow public all on reference_data" 
ON public.reference_data FOR ALL USING (true);

CREATE POLICY "Allow public read on audit_logs" 
ON public.audit_logs FOR SELECT USING (true);

CREATE POLICY "Allow public all on audit_logs" 
ON public.audit_logs FOR ALL USING (true);
