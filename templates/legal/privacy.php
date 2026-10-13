<article class="r-window legal-window">
    <div class="r-titlebar"><h1 class="r-titlebar-title">Privacy &amp; cookies</h1></div>
    <div class="r-card-body prose">
        <p class="r-muted">Last updated 13 October 2026</p>

        <p>Retro Records is a personal portfolio project. It collects only what it needs to let you sign in and keep your
           own server list.</p>

        <h2>What we store</h2>
        <ul>
            <li><strong>Account details:</strong> your username, email address and a one-way Argon2id hash of your
                password. Your actual password is never stored.</li>
            <li><strong>Your records:</strong> the hostnames, IP addresses, locations, statuses and notes you enter.</li>
            <li><strong>Security data:</strong> a keyed hash of your IP address (not the address itself) when you submit
                forms. It is used for rate limiting and deleted after 24 hours. We also record your last sign-in time
                and how many failed sign-ins your account has had, so brute-force attempts can be blocked.</li>
        </ul>

        <h2>Cookies</h2>
        <p>We set exactly one cookie, <code>__Host-rrsid</code>. It is a strictly necessary session cookie: it keeps you
           signed in and protects forms against cross-site request forgery. It is <code>HttpOnly</code>,
           <code>Secure</code> and <code>SameSite=Strict</code>. It is deleted when you close your browser, and the session behind it
           expires after 15 minutes of inactivity. Because it is essential, there is no cookie banner. There are no
           analytics, advertising or third-party cookies, and no third-party scripts.</p>

        <h2>Where it lives</h2>
        <p>The app runs on Vercel. Data is stored in a PostgreSQL database hosted by Supabase in the AWS
           ap-south-1 (Mumbai) region. Connections are encrypted in transit.</p>

        <h2>How long we keep it</h2>
        <p>Your account and records stay until you delete them. You can delete your account at any time from
           <a href="/account">My account</a>. This removes your account and every record you've added, immediately and
           permanently. Session data is removed within 24 hours of your last activity.</p>

        <h2>Your rights and contact</h2>
        <p>We don't sell or share your data. To ask a question or request a copy of your data, email
           <a href="mailto:fazalabbas2002@gmail.com">fazalabbas2002@gmail.com</a>.</p>
    </div>
</article>
