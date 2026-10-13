create schema if not exists retro_records;
set search_path = retro_records;

create table if not exists users (
    id             bigint generated always as identity primary key,
    username       varchar(32)  not null,
    email          varchar(254) not null,
    password_hash  varchar(255) not null,
    role           varchar(10)  not null default 'user' check (role in ('user', 'admin')),
    failed_logins  smallint     not null default 0,
    locked_until   timestamptz,
    last_login_at  timestamptz,
    created_at     timestamptz  not null default now()
);
create unique index if not exists users_username_uq on users (lower(username));
create unique index if not exists users_email_uq on users (lower(email));

create table if not exists assets (
    id           bigint generated always as identity primary key,
    owner_id     bigint       not null references users (id) on delete cascade,
    hostname     varchar(253) not null,
    ip_address   inet,
    location     varchar(120) not null default '',
    environment  varchar(12)  not null check (environment in ('production', 'staging', 'development')),
    status       varchar(12)  not null check (status in ('online', 'maintenance', 'offline')),
    notes        varchar(2000) not null default '',
    created_at   timestamptz  not null default now(),
    updated_at   timestamptz  not null default now()
);
create unique index if not exists assets_owner_hostname_uq on assets (owner_id, lower(hostname));
create index if not exists assets_owner_updated_idx on assets (owner_id, updated_at desc);

create table if not exists sessions (
    id             varchar(128) primary key,
    data           text         not null default '',
    last_activity  timestamptz  not null default now()
);
create index if not exists sessions_last_activity_idx on sessions (last_activity);

create table if not exists rate_hits (
    id        bigint generated always as identity primary key,
    bucket    varchar(32) not null,
    key_hash  char(64)    not null,
    hit_at    timestamptz not null default now()
);
create index if not exists rate_hits_lookup_idx on rate_hits (bucket, key_hash, hit_at);

alter table assets enable row level security;
drop policy if exists assets_owner_only on assets;
create policy assets_owner_only on assets
    using (owner_id = nullif(current_setting('app.user_id', true), '')::bigint)
    with check (owner_id = nullif(current_setting('app.user_id', true), '')::bigint);

revoke all on schema retro_records from public;
