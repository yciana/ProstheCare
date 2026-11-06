# ProstheCare Clinic Management System

A full-stack clinical management web application for prosthetic and orthotic care teams, converted directly from the Figma design into clean **PHP**, strictly external **CSS** (no inline CSS), and integrated with **Supabase (PostgreSQL & Auth)**. Fully configured for **VS Code** development and **Vercel** serverless deployment.

---

## 🎨 Figma Design Implementation Details

- **Pixel-Perfect Split Layout**:
  - **Left Hero Panel**: Deep clinical teal gradient (`#0d797f` → `#07474b`), official ProstheCare rounded badge with medical plus icon, patient-centered care typography, geometric orbital radar rings with pulsing amber marker, and encrypted security shield note.
  - **Right Authentication Panel**: Clean slate & white typography, floating input fields, interactive SVG password show/hide toggle, right-aligned forgot password link, full-width teal action button, and smooth transitions.
- **Zero Inline CSS**: 100% of styling is modularized across `css/variables.css`, `css/auth.css`, and `css/dashboard.css`.

---

## 📁 Project Structure

```
prosthecare-clinic/
├── .vscode/
│   ├── launch.json            # VS Code launch debug configuration
│   ├── tasks.json             # VS Code task to run "php -S localhost:8000"
│   └── settings.json          # Editor formatting preferences
├── config/
│   └── supabase.php           # Environment loader & configuration handler
├── css/
│   ├── variables.css          # Design tokens, color palette, typography, radii
│   ├── auth.css               # External CSS for Sign In, Register, Forgot Password
│   └── dashboard.css          # External CSS for Clinic Workspace & Management
├── includes/
│   ├── SupabaseClient.php     # Object-Oriented Supabase REST API & Auth Client
│   ├── auth_check.php         # Session guard & route protector
│   ├── header.php             # Reusable clinical workspace navigation & header
│   └── footer.php             # Workspace footer script
├── js/
│   ├── auth.js                # Password visibility toggle & validation
│   └── dashboard.js           # Modal dialogs & live table search filtering
├── supabase/
│   └── schema.sql             # Complete PostgreSQL DDL, RLS policies & seed data
├── .env.example               # Template for Supabase credentials
├── .env                       # Local environment variables
├── .vercelignore              # Ignore rules for Vercel deployment
├── appointments.php           # Fitting session schedule & appointment booking
├── care-plans.php             # Prosthetic socket designs & fabrication tracking
├── dashboard.php              # Main clinical dashboard with statistics & active cases
├── forgot-password.php        # Password recovery workflow
├── index.php                  # Exact Figma Sign In Page
├── logout.php                 # Session & cookie destruction
├── patients.php               # Prosthetic patient directory & registration modal
├── register.php               # Practitioner account creation
├── vercel.json                # Vercel serverless deployment configuration
└── README.md                  # Complete documentation & deployment guide
```

---

## 🚀 Quick Start in VS Code

### 1. Open the Project in VS Code
Open VS Code and choose **File > Open Folder...**, then select:
```
C:\Users\ederw\.gemini\antigravity\scratch\prosthecare-clinic
```

### 2. Start the Local PHP Server
You have two easy ways to start the server:

**Option A (Terminal):**
Open the integrated terminal (`Ctrl + \``) in VS Code and run:
```bash
php -S localhost:8000
```

**Option B (VS Code Task):**
Press `Ctrl + Shift + B` (or `Cmd + Shift + B` on macOS) to trigger the pre-configured **Start ProstheCare PHP Server** build task.

### 3. Open in Browser
Visit [http://localhost:8000](http://localhost:8000).

> **Note on Demo Mode:**
> The app includes a built-in session demo store. Even before configuring Supabase, you can immediately test the Sign In screen using:
> - **Email:** `admin@prosthecare.com`
> - **Password:** `admin123`

---

## 🗄️ Setting Up Supabase Database

### Step 1: Create a Supabase Project
1. Go to [https://supabase.com](https://supabase.com) and sign in or sign up.
2. Click **New Project** and name it (e.g., `prosthecare-clinic`).
3. Set your database password and choose your nearest region.

### Step 2: Run the SQL Schema
1. In your Supabase project dashboard, navigate to the **SQL Editor** on the left menu.
2. Click **New query**.
3. Copy and paste the entire contents of [`supabase/schema.sql`](file:///C:/Users/ederw/.gemini/antigravity/scratch/prosthecare-clinic/supabase/schema.sql) into the editor.
4. Click **Run**.
   *This automatically creates the `profiles`, `patients`, `appointments`, and `care_plans` tables, configures Row Level Security (RLS) policies, and populates realistic prosthetic case seed records.*

### Step 3: Connect Credentials in `.env`
1. In your Supabase dashboard, go to **Project Settings** (gear icon) > **API**.
2. Copy the **Project URL** and the **anon public** key.
3. Open the `.env` file in your project root and paste them:
   ```env
   SUPABASE_URL=https://your-project-ref.supabase.co
   SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6...
   ```
4. Refresh your browser at [http://localhost:8000](http://localhost:8000). The top status indicator will now show **Supabase Live** with a green indicator!

---

## ☁️ Deploying to Vercel

The project includes a ready-to-use [`vercel.json`](file:///C:/Users/ederw/.gemini/antigravity/scratch/prosthecare-clinic/vercel.json) leveraging `vercel-php` community runtime for serverless PHP execution.

### Method 1: Deploy with Vercel CLI

1. Install the Vercel CLI (if not already installed):
   ```bash
   npm install -g vercel
   ```
2. In the `prosthecare-clinic` directory, run:
   ```bash
   vercel
   ```
3. Follow the CLI prompts to link and deploy your project.
4. Add your Supabase environment variables on Vercel:
   ```bash
   vercel env add SUPABASE_URL
   vercel env add SUPABASE_ANON_KEY
   ```
5. Deploy to production:
   ```bash
   vercel --prod
   ```

### Method 2: Deploy via GitHub & Vercel Dashboard

1. Initialize a git repository and push your project to GitHub:
   ```bash
   git init
   git add .
   git commit -m "feat: initial ProstheCare clinic management system"
   git branch -M main
   git remote add origin https://github.com/your-username/prosthecare-clinic.git
   git push -u origin main
   ```
2. Go to [https://vercel.com/new](https://vercel.com/new).
3. Import your GitHub repository.
4. Under **Environment Variables**, add:
   - `SUPABASE_URL`: Your Supabase project URL
   - `SUPABASE_ANON_KEY`: Your Supabase anon public key
5. Click **Deploy**. Vercel will build the static CSS/JS assets and serverless PHP endpoints automatically!

---

## ✨ Features Summary

| Feature | Description |
| :--- | :--- |
| **Figma Sign In** | Pixel-accurate rendition of node `0-6` with responsive desktop & mobile support |
| **Strict No-Inline CSS** | All styles segregated into external CSS files with reusable tokens |
| **Supabase Authentication** | Live sign-in, registration, and password recovery via Supabase Auth API |
| **Prosthetic Directory** | Track client amputation levels, K-levels, and socket fitting milestones |
| **Fitting Lab Appointments** | Schedule dynamic gait alignments and diagnostic check socket evaluations |
| **Device Care Plans** | Track componentry (microprocessor knees, vacuum systems, carbon feet) |
| **Instant Fallback Demo** | Works seamlessly out of the box even before cloud setup |
