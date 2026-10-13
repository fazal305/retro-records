-- Run after schema.sql as a superuser. Replace the password before running.
do $$ begin
    if not exists (select 1 from pg_roles where rolname = 'retro_app') then
        create role retro_app login connection limit 20 password 'CHANGE_ME';
    end if;
end $$;

alter role retro_app set search_path = retro_records;
revoke all on schema retro_records from anon, authenticated;
grant usage on schema retro_records to retro_app;
grant select, insert, update, delete on all tables in schema retro_records to retro_app;
