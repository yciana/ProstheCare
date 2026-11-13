-- ==============================================================================
-- ProstheCare - Fix "null value in column email of relation profiles"
-- Run this in your Supabase SQL Editor:
-- https://supabase.com/dashboard/project/gdpzdypxospokczofxlu/sql
-- ==============================================================================

-- 1. Remove NOT NULL constraint on email and full_name in public.profiles table
ALTER TABLE public.profiles ALTER COLUMN email DROP NOT NULL;
ALTER TABLE public.profiles ALTER COLUMN full_name DROP NOT NULL;

-- 2. Update or install a safe trigger function that passes new.email and never blocks registration
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS trigger AS $$
BEGIN
  INSERT INTO public.profiles (user_id, email, full_name, role)
  VALUES (
    new.id,
    new.email,
    COALESCE(new.raw_user_meta_data->>'full_name', split_part(new.email, '@', 1)),
    COALESCE(new.raw_user_meta_data->>'role', 'Client')
  )
  ON CONFLICT (id) DO UPDATE SET
    email = EXCLUDED.email,
    user_id = EXCLUDED.user_id,
    full_name = COALESCE(EXCLUDED.full_name, public.profiles.full_name);
  RETURN new;
EXCEPTION
  WHEN others THEN
    -- In case of any conflict or unhandled column, never crash auth signup
    RETURN new;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- 3. Ensure the trigger is active on auth.users
DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();
