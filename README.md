# \# PulseDesk

# 

# A multi-tenant support desk SaaS built with the Eean multi-agent methodology.

# 

# \## Stack

# \- Backend: Laravel 11 + Sanctum

# \- Frontend: React 19 + Vite + Tailwind

# \- Database: MySQL 8

# \- Tests: Pest/PHPUnit

# \- CI: GitHub Actions

# 

# \## Agent Architecture

# \- \*\*Hermes\*\* (orchestrator): z-ai/glm-4.5-air via EastRouter — plans tasks, assigns to OpenClaw

# \- \*\*OpenClaw\*\* (executor): z-ai/glm-4.7 via EastRouter — writes code, runs commands, reports back

# \- \*\*Slack\*\*: communication backbone — all tasks assigned and reported via Slack channels

# \- \*\*Human\*\*: reviews and approves in #human-review before every merge

# 

# \## Run Steps

# ```bash

# \# Backend

# cd backend

# composer install --ignore-platform-reqs

# cp .env.example .env

# \# Set DB\_DATABASE=pulsedesk DB\_USERNAME=root DB\_PASSWORD=yourpassword

# php artisan key:generate

# php artisan migrate --seed

# php artisan serve

# 

# \# Frontend

# cd frontend

# npm install

# npm run dev

# ```

# 

# \## Features

# \- Multi-tenancy (tenant isolation via global scope)

# \- Roles: admin / agent / customer

# \- Ticket CRUD with filters and search

# \- Threaded replies

# \- Seeded demo data

# \- GitHub Actions CI

# 

# \## Models Used

# \- Hermes: z-ai/glm-4.5-air (EastRouter)

# \- OpenClaw: z-ai/glm-4.7 (EastRouter)

# 

# \## Sprints Run

# \- Sprint 1: Backend core (migrations, auth, tickets, seeders)

# \- Sprint 2: Frontend (React, Login, Dashboard)

