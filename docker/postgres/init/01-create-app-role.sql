-- Create non-superuser role for application with RLS enforcement
DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'steriqore_app') THEN
        CREATE ROLE steriqore_app WITH LOGIN PASSWORD 'steriqore_app_secret' NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT;
    END IF;
END
$$;

GRANT CONNECT ON DATABASE steriqore TO steriqore_app;

\connect steriqore

GRANT USAGE, CREATE ON SCHEMA public TO steriqore_app;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO steriqore_app;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO steriqore_app;
GRANT ALL PRIVILEGES ON ALL ROUTINES IN SCHEMA public TO steriqore_app;

ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO steriqore_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO steriqore_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON FUNCTIONS TO steriqore_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON ROUTINES TO steriqore_app;
