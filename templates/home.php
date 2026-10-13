<div class="r-window home-window">
    <div class="r-titlebar">
        <span class="r-titlebar-title">Welcome to Retro Records</span>
    </div>
    <div class="r-card-body">
        <h1>Your server inventory, the 2006 way.</h1>
        <p class="lead">
            Keep track of hostnames, IP addresses, environments and status for every machine you look after.
            It looks like a classic web dashboard, but the security underneath is current.
        </p>
        <div class="r-flex r-gap-sm r-flex-wrap">
            <a class="r-btn r-btn-primary r-btn-lg" href="/register">Create a free account</a>
            <a class="r-btn r-btn-lg" href="/login">Sign in</a>
        </div>
    </div>
</div>

<div class="r-grid r-grid-3 feature-grid">
    <section class="r-card">
        <div class="r-titlebar"><h2 class="r-titlebar-title">Private by default</h2></div>
        <div class="r-card-body">
            <p>Each account only ever sees its own servers. Ownership is checked in the PHP code and again by
               row-level security in PostgreSQL.</p>
        </div>
    </section>
    <section class="r-card">
        <div class="r-titlebar"><h2 class="r-titlebar-title">Locked-down sign-in</h2></div>
        <div class="r-card-body">
            <p>Argon2id password hashing, 15-minute idle sessions, CSRF tokens on every change, and an account
               lock after 5 failed sign-ins.</p>
        </div>
    </section>
    <section class="r-card">
        <div class="r-titlebar"><h2 class="r-titlebar-title">Find things fast</h2></div>
        <div class="r-card-body">
            <p>Search by hostname, IP or location, filter by environment and status, and edit records in place
               without leaving the table.</p>
        </div>
    </section>
</div>

<p class="r-muted demo-note">
    This is a portfolio project. Please don't store real production credentials or sensitive infrastructure details here.
</p>
