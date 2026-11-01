<style>
    .pr { --maroon:#7b1a33; --maroon-dk:#531a35; --blush:#f9eef0; --blush-dk:#f6dfe4; --ink:#3a1020; --muted:#8a7078; --line:#dccbd0; color:var(--ink); }
    .pr, .pr * { box-sizing:border-box; }
    .pr h1 { font-size:22px; margin:0 0 4px; color:var(--maroon-dk); font-weight:700; }
    .pr .sub { margin:0; font-size:13px; color:var(--muted); }
    .pr .head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .pr .btns { display:flex; gap:10px; }
    .pr .btn { border:1px solid var(--line); background:#fff; color:var(--ink); border-radius:8px; padding:9px 16px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; font-family:inherit; }
    .pr .btn:hover { background:var(--blush); }
    .pr .btn.primary { background:var(--maroon-dk); border-color:var(--maroon-dk); color:#fff; }
    .pr .btn.primary:hover { background:var(--maroon); }
    .pr .card { background:#fff; border-radius:14px; padding:20px; }
    .pr input, .pr select, .pr textarea { border:1px solid var(--line); border-radius:8px; padding:10px 12px; font-size:13px; background:#fff; color:var(--ink); width:100%; font-family:inherit; }
    .pr :focus-visible { outline:2px solid var(--maroon); outline-offset:2px; }
    .pr .toast { position:fixed; right:20px; bottom:20px; background:var(--maroon-dk); color:#fff; padding:12px 18px; border-radius:10px; font-size:13px; z-index:1100; opacity:0; transition:opacity .2s; pointer-events:none; }
    .pr .toast.show { opacity:1; } .pr .toast.error { background:#b3261e; }
</style>
