import { Hono } from 'hono'
import { cors } from 'hono/cors'

const app = new Hono()
app.use('/api/*', cors())

// ── Mock Data ──
const USERS = [
  { id: 1, username: 'admin', password: 'admin123', email: 'admin@void.dev', role: 'admin' },
  { id: 2, username: 'user', password: 'user123', email: 'user@void.dev', role: 'user' },
]

const SERVERS = [
  { id: 's1', name: 'Pyro', address: 'public.pyro.sh:25565', status: 'online', cpu: 12.4, ram: '2.1 GB', storage: '4.8 GB', type: 'Minecraft', uptime: '14d 6h 23m', players: '12/50' },
  { id: 's2', name: 'Velvet', address: 'velvet.pyro.sh:25566', status: 'online', cpu: 8.2, ram: '1.8 GB', storage: '3.2 GB', type: 'Minecraft', uptime: '7d 12h 45m', players: '8/30' },
  { id: 's3', name: 'Dev Secrets', address: 'frens.pyro.sh:8081', status: 'installing', cpu: 0, ram: '0 Bytes', storage: '0 Bytes', type: 'NodeJS', uptime: '-', players: '-' },
  { id: 's4', name: 'Creative Hub', address: 'hub.pyro.sh:25567', status: 'offline', cpu: 0, ram: '0 Bytes', storage: '512 MB', type: 'Minecraft', uptime: '-', players: '0/20' },
]

const FILES = [
  { name: '.cache', type: 'folder', date: 'Dec 7', size: '-' },
  { name: 'config', type: 'folder', date: 'Dec 14', size: '-' },
  { name: 'defaultconfigs', type: 'folder', date: 'Dec 25', size: '-' },
  { name: 'journeymap', type: 'folder', date: 'Nov 1', size: '-' },
  { name: 'libraries', type: 'folder', date: 'Oct 27', size: '245 MB' },
  { name: 'logs', type: 'folder', date: 'Dec 7', size: '-' },
  { name: 'modernfix', type: 'folder', date: 'Dec 27', size: '-' },
  { name: 'mods', type: 'folder', date: 'Oct 27', size: '128 MB' },
  { name: 'patchouli_books', type: 'folder', date: 'Dec 25', size: '-' },
  { name: 'versions', type: 'folder', date: 'Nov 23', size: '-' },
  { name: 'world', type: 'folder', date: 'Dec 7', size: '1.2 GB' },
  { name: 'audit.log', type: 'file', date: 'Dec 7', size: '24 KB' },
  { name: 'banned-ips.json', type: 'file', date: 'Dec 7', size: '2 KB' },
  { name: 'banned-players.json', type: 'file', date: 'Dec 7', size: '1 KB' },
  { name: 'eula.txt', type: 'file', date: 'Dec 7', size: '181 B' },
  { name: 'server.properties', type: 'file', date: 'Dec 14', size: '1.4 KB' },
]

// ── API Routes ──
app.post('/api/login', async (c) => {
  const { username, password } = await c.req.json()
  const user = USERS.find(u => u.username === username && u.password === password)
  if (user) return c.json({ success: true, user: { id: user.id, username: user.username, email: user.email, role: user.role } })
  return c.json({ success: false, message: 'Invalid credentials' }, 401)
})

app.get('/api/servers', (c) => c.json(SERVERS))
app.get('/api/servers/:id', (c) => {
  const s = SERVERS.find(s => s.id === c.req.param('id'))
  return s ? c.json(s) : c.json({ error: 'Not found' }, 404)
})
app.get('/api/servers/:id/files', (c) => c.json(FILES))
app.get('/api/user', (c) => c.json(USERS[0]))

// ── Login Page ──
app.get('/login', (c) => c.html(loginPage()))
app.get('/', (c) => c.html(dashboardPage()))
app.get('/server/:id', (c) => c.html(dashboardPage()))
app.get('/server/:id/files', (c) => c.html(dashboardPage()))
app.get('/server/:id/console', (c) => c.html(dashboardPage()))
app.get('/settings', (c) => c.html(dashboardPage()))
app.get('/api-keys', (c) => c.html(dashboardPage()))
app.get('/license', (c) => c.html(dashboardPage()))

function loginPage() {
  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Void Panel — Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            void: { 50:'#fef2f2',100:'#fee2e2',200:'#fecaca',300:'#fca5a5',400:'#f87171',500:'#ef4444',600:'#dc2626',700:'#b91c1c',800:'#991b1b',900:'#7f1d1d',950:'#450a0a' },
            dark: { 50:'#1a1a2e',100:'#16162a',200:'#121225',300:'#0e0e1f',400:'#0a0a18',500:'#080814',600:'#060610',700:'#04040c',800:'#020208',900:'#010104',950:'#000000' }
          },
          fontFamily: { sans: ['Inter', 'sans-serif'] }
        }
      }
    }
  </script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .glow-red { box-shadow: 0 0 40px rgba(220, 38, 38, 0.15), 0 0 80px rgba(220, 38, 38, 0.05); }
    .bg-grid { background-image: radial-gradient(rgba(220, 38, 38, 0.06) 1px, transparent 1px); background-size: 24px 24px; }
    @keyframes float { 0%,100% { transform: translateY(0px); } 50% { transform: translateY(-12px); } }
    .float-anim { animation: float 6s ease-in-out infinite; }
    @keyframes pulse-ring { 0% { transform: scale(0.8); opacity: 1; } 100% { transform: scale(2.2); opacity: 0; } }
    .pulse-ring { animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite; }
    .input-glow:focus { box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2); }
  </style>
</head>
<body class="bg-dark-950 min-h-screen flex items-center justify-center bg-grid overflow-hidden relative">
  <!-- Animated background orbs -->
  <div class="absolute top-20 left-20 w-72 h-72 bg-void-600/5 rounded-full blur-3xl float-anim"></div>
  <div class="absolute bottom-20 right-20 w-96 h-96 bg-void-700/5 rounded-full blur-3xl float-anim" style="animation-delay:-3s"></div>
  <div class="absolute top-1/2 left-1/3 w-48 h-48 bg-void-500/3 rounded-full blur-2xl float-anim" style="animation-delay:-1.5s"></div>

  <div class="relative z-10 w-full max-w-md mx-4">
    <!-- Logo -->
    <div class="text-center mb-10">
      <div class="relative inline-block">
        <div class="absolute inset-0 bg-void-600/20 rounded-full blur-xl pulse-ring"></div>
        <div class="relative w-20 h-20 mx-auto bg-gradient-to-br from-void-600 to-void-800 rounded-2xl flex items-center justify-center shadow-2xl transform rotate-3 hover:rotate-0 transition-transform duration-500">
          <i class="fas fa-dragon text-3xl text-white"></i>
        </div>
      </div>
      <h1 class="text-3xl font-extrabold text-white mt-6 tracking-tight">Void<span class="text-void-500">Panel</span></h1>
      <p class="text-gray-500 text-sm mt-2">Game Server Management Platform</p>
    </div>

    <!-- Login Card -->
    <div class="glow-red bg-dark-100/80 backdrop-blur-xl rounded-2xl border border-white/5 p-8">
      <div id="error-msg" class="hidden mb-4 p-3 bg-void-900/50 border border-void-700/50 rounded-xl text-void-400 text-sm text-center">
        <i class="fas fa-exclamation-circle mr-1"></i> <span id="error-text">Invalid credentials</span>
      </div>

      <form id="loginForm" class="space-y-5">
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Username</label>
          <div class="relative">
            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
            <input id="username" type="text" placeholder="Enter your username" 
              class="input-glow w-full bg-dark-300/60 border border-white/5 rounded-xl pl-11 pr-4 py-3.5 text-white placeholder-gray-600 focus:border-void-600 focus:outline-none transition-all text-sm" autocomplete="username">
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Password</label>
          <div class="relative">
            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
            <input id="password" type="password" placeholder="Enter your password"
              class="input-glow w-full bg-dark-300/60 border border-white/5 rounded-xl pl-11 pr-12 py-3.5 text-white placeholder-gray-600 focus:border-void-600 focus:outline-none transition-all text-sm" autocomplete="current-password">
            <button type="button" onclick="togglePw()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
              <i id="pw-icon" class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
            <input type="checkbox" class="w-4 h-4 rounded border-white/10 bg-dark-300 text-void-600 focus:ring-void-600 focus:ring-offset-0">
            <span>Remember me</span>
          </label>
          <a href="#" class="text-sm text-void-500 hover:text-void-400 transition-colors">Forgot password?</a>
        </div>

        <button type="submit" id="login-btn"
          class="w-full bg-gradient-to-r from-void-600 to-void-700 hover:from-void-500 hover:to-void-600 text-white font-semibold py-3.5 rounded-xl transition-all duration-300 shadow-lg shadow-void-600/20 hover:shadow-void-500/30 active:scale-[0.98] flex items-center justify-center gap-2">
          <span id="btn-text">Sign In</span>
          <i id="btn-spinner" class="fas fa-circle-notch fa-spin hidden"></i>
        </button>
      </form>

      <div class="mt-6 flex items-center gap-3">
        <div class="flex-1 h-px bg-white/5"></div>
        <span class="text-xs text-gray-600 uppercase tracking-widest">Demo Credentials</span>
        <div class="flex-1 h-px bg-white/5"></div>
      </div>
      <div class="mt-3 grid grid-cols-2 gap-2">
        <button onclick="fillDemo('admin','admin123')" class="text-xs bg-dark-300/40 hover:bg-dark-200/60 border border-white/5 rounded-lg px-3 py-2 text-gray-400 hover:text-white transition-all">
          <i class="fas fa-shield-alt text-void-500 mr-1"></i> Admin
        </button>
        <button onclick="fillDemo('user','user123')" class="text-xs bg-dark-300/40 hover:bg-dark-200/60 border border-white/5 rounded-lg px-3 py-2 text-gray-400 hover:text-white transition-all">
          <i class="fas fa-user text-void-500 mr-1"></i> User
        </button>
      </div>
    </div>

    <!-- Footer -->
    <div class="text-center mt-8 space-y-2">
      <p class="text-gray-600 text-xs">Powered by <span class="text-void-500 font-semibold">Void Development</span></p>
      <p class="text-gray-700 text-[10px]">&copy; 2024-2026 Void Development. All rights reserved. <a href="/license" class="text-void-600 hover:text-void-500">License</a></p>
    </div>
  </div>

  <script>
    function togglePw() { const p=document.getElementById('password'),i=document.getElementById('pw-icon'); if(p.type==='password'){p.type='text';i.className='fas fa-eye-slash'}else{p.type='password';i.className='fas fa-eye'} }
    function fillDemo(u,p) { document.getElementById('username').value=u; document.getElementById('password').value=p; }
    document.getElementById('loginForm').addEventListener('submit', async(e)=>{
      e.preventDefault();
      const btn=document.getElementById('login-btn'), txt=document.getElementById('btn-text'), spin=document.getElementById('btn-spinner'), err=document.getElementById('error-msg');
      txt.textContent='Signing in...'; spin.classList.remove('hidden'); btn.disabled=true; err.classList.add('hidden');
      try {
        const r = await fetch('/api/login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:document.getElementById('username').value,password:document.getElementById('password').value})});
        const d = await r.json();
        if(d.success){localStorage.setItem('void_user',JSON.stringify(d.user)); window.location.href='/'}
        else{document.getElementById('error-text').textContent=d.message||'Invalid credentials'; err.classList.remove('hidden')}
      }catch(ex){document.getElementById('error-text').textContent='Connection error'; err.classList.remove('hidden')}
      txt.textContent='Sign In'; spin.classList.add('hidden'); btn.disabled=false;
    });
    if(localStorage.getItem('void_user')) window.location.href='/';
  </script>
</body>
</html>`
}

function dashboardPage() {
  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Void Panel — Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            void: { 50:'#fef2f2',100:'#fee2e2',200:'#fecaca',300:'#fca5a5',400:'#f87171',500:'#ef4444',600:'#dc2626',700:'#b91c1c',800:'#991b1b',900:'#7f1d1d',950:'#450a0a' },
            dark: { 50:'#1a1a2e',100:'#16162a',200:'#121225',300:'#0e0e1f',400:'#0a0a18',500:'#080814',600:'#060610',700:'#04040c',800:'#020208',900:'#010104',950:'#000000' }
          },
          fontFamily: { sans: ['Inter', 'sans-serif'] }
        }
      }
    }
  </script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.15); }
    .sidebar-item { transition: all 0.2s ease; }
    .sidebar-item:hover { background: rgba(255,255,255,0.04); }
    .sidebar-item.active { color: #ef4444; background: rgba(239,68,68,0.06); }
    .sidebar-item.active::before { content: ''; position: absolute; left: 0; top: 4px; bottom: 4px; width: 3px; background: #ef4444; border-radius: 0 3px 3px 0; }
    .server-card { transition: all 0.2s ease; }
    .server-card:hover { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); transform: translateY(-1px); }
    .file-row { transition: all 0.15s ease; }
    .file-row:hover { background: rgba(255,255,255,0.03); }
    .file-row.selected { background: rgba(239,68,68,0.08); }
    .stat-card { transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-2px); border-color: rgba(239,68,68,0.3); }
    .console-text { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
    @keyframes slideIn { from { opacity:0; transform: translateX(-8px); } to { opacity:1; transform: translateX(0); } }
    .animate-in { animation: slideIn 0.3s ease forwards; }
    @keyframes fadeUp { from { opacity:0; transform: translateY(12px); } to { opacity:1; transform: translateY(0); } }
    .fade-up { animation: fadeUp 0.4s ease forwards; }
    .badge-online { background: rgba(34,197,94,0.1); color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
    .badge-offline { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }
    .badge-installing { background: rgba(234,179,8,0.1); color: #eab308; border: 1px solid rgba(234,179,8,0.2); }
  </style>
</head>
<body class="bg-dark-950 text-gray-300 min-h-screen flex">

  <!-- Sidebar -->
  <aside id="sidebar" class="w-64 bg-dark-900/80 border-r border-white/5 flex flex-col min-h-screen fixed left-0 top-0 z-40 backdrop-blur-xl">
    <!-- Brand -->
    <div class="p-5 flex items-center gap-3 border-b border-white/5">
      <div class="w-10 h-10 bg-gradient-to-br from-void-600 to-void-800 rounded-xl flex items-center justify-center shadow-lg">
        <i class="fas fa-dragon text-white text-lg"></i>
      </div>
      <div>
        <span class="font-bold text-white text-lg tracking-tight">Void<span class="text-void-500">Panel</span></span>
        <p class="text-[10px] text-gray-600 -mt-0.5">v2.1.0</p>
      </div>
      <button class="ml-auto text-gray-500 hover:text-white transition-colors p-1">
        <i class="fas fa-ellipsis-h"></i>
      </button>
    </div>

    <!-- Main Nav (shows when no server selected) -->
    <nav id="nav-main" class="flex-1 p-3 space-y-1">
      <a href="/" onclick="navigate(event,'/')" class="sidebar-item active relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium" data-page="servers">
        <i class="fas fa-server w-5 text-center"></i> <span>Servers</span>
      </a>
      <a href="/api-keys" onclick="navigate(event,'/api-keys')" class="sidebar-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium" data-page="api-keys">
        <i class="fas fa-key w-5 text-center"></i> <span>API Keys</span>
      </a>
      <a href="#" class="sidebar-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium">
        <i class="fas fa-fingerprint w-5 text-center"></i> <span>SSH Keys</span>
      </a>
      <a href="/settings" onclick="navigate(event,'/settings')" class="sidebar-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium" data-page="settings">
        <i class="fas fa-cog w-5 text-center"></i> <span>Settings</span>
      </a>
    </nav>

    <!-- Server Nav (shows when server selected) -->
    <nav id="nav-server" class="flex-1 p-3 space-y-1 hidden">
      <button onclick="navigate(event,'/')" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-gray-500 hover:text-white hover:bg-white/5 transition-all mb-2">
        <i class="fas fa-arrow-left"></i> <span>Back to Servers</span>
      </button>
      <div id="server-nav-name" class="px-3 py-2 mb-2">
        <h3 class="font-bold text-white text-sm">Server</h3>
        <p class="text-[11px] text-gray-500">address</p>
      </div>
      <a href="#" class="sidebar-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium" data-page="console" onclick="showServerPage(event,'console')">
        <i class="fas fa-terminal w-5 text-center"></i> <span>Console</span>
      </a>
      <a href="#" class="sidebar-item active relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium" data-page="files" onclick="showServerPage(event,'files')">
        <i class="fas fa-folder w-5 text-center"></i> <span>Files</span>
      </a>
      <a href="#" class="sidebar-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium" data-page="databases" onclick="showServerPage(event,'databases')">
        <i class="fas fa-database w-5 text-center"></i> <span>Databases</span>
      </a>
      <a href="#" class="sidebar-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium" data-page="backups" onclick="showServerPage(event,'backups')">
        <i class="fas fa-cloud-download-alt w-5 text-center"></i> <span>Backups</span>
      </a>
      <a href="#" class="sidebar-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium" data-page="networking" onclick="showServerPage(event,'networking')">
        <i class="fas fa-network-wired w-5 text-center"></i> <span>Networking</span>
      </a>
      <a href="#" class="sidebar-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium" data-page="server-settings" onclick="showServerPage(event,'server-settings')">
        <i class="fas fa-sliders-h w-5 text-center"></i> <span>Settings</span>
      </a>
    </nav>

    <!-- User & Footer -->
    <div class="p-3 border-t border-white/5">
      <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/5 cursor-pointer transition-all">
        <div class="w-8 h-8 bg-gradient-to-br from-void-600 to-purple-700 rounded-lg flex items-center justify-center text-white text-xs font-bold" id="user-avatar">A</div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-white truncate" id="user-name">Admin</p>
          <p class="text-[10px] text-gray-500" id="user-role">Administrator</p>
        </div>
        <button onclick="logout()" class="text-gray-500 hover:text-void-500 transition-colors" title="Logout">
          <i class="fas fa-sign-out-alt"></i>
        </button>
      </div>
      <div class="mt-3 pt-3 border-t border-white/5 text-center">
        <p class="text-[10px] text-gray-600">Powered by <span class="text-void-500 font-semibold">Void Development</span></p>
        <p class="text-[9px] text-gray-700 mt-0.5">&copy; 2024-2026 All rights reserved</p>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="ml-64 flex-1 min-h-screen">
    <!-- Top Bar -->
    <header class="sticky top-0 z-30 bg-dark-950/80 backdrop-blur-xl border-b border-white/5 px-6 py-4 flex items-center justify-between">
      <div id="page-breadcrumb" class="flex items-center gap-2 text-sm">
        <i class="fas fa-home text-gray-500"></i>
        <span class="text-gray-500">/</span>
        <span class="text-white font-medium" id="breadcrumb-text">Your Servers</span>
      </div>
      <div class="flex items-center gap-3">
        <button class="p-2 text-gray-500 hover:text-white hover:bg-white/5 rounded-lg transition-all" title="Notifications">
          <i class="fas fa-bell"></i>
        </button>
        <button onclick="toggleTheme()" class="p-2 text-gray-500 hover:text-white hover:bg-white/5 rounded-lg transition-all" title="Theme">
          <i class="fas fa-moon"></i>
        </button>
        <div class="flex gap-1 bg-dark-200/50 rounded-lg p-1 border border-white/5">
          <button onclick="setView('list')" id="view-list" class="p-1.5 rounded text-gray-500 hover:text-white transition-all bg-white/5">
            <i class="fas fa-list text-xs"></i>
          </button>
          <button onclick="setView('grid')" id="view-grid" class="p-1.5 rounded text-gray-500 hover:text-white transition-all">
            <i class="fas fa-th text-xs"></i>
          </button>
        </div>
      </div>
    </header>

    <!-- Content Area -->
    <div id="content" class="p-6">
      <!-- Will be populated by JS -->
    </div>
  </main>

  <script>
    // ── State ──
    let currentView = 'list';
    let servers = [];
    let currentServer = null;
    let currentServerPage = 'files';
    const user = JSON.parse(localStorage.getItem('void_user') || 'null');
    if (!user) window.location.href = '/login';

    // ── Init ──
    if (user) {
      document.getElementById('user-avatar').textContent = user.username[0].toUpperCase();
      document.getElementById('user-name').textContent = user.username;
      document.getElementById('user-role').textContent = user.role === 'admin' ? 'Administrator' : 'User';
      loadServers();
      routePage();
    }

    async function loadServers() {
      try {
        const r = await fetch('/api/servers');
        servers = await r.json();
      } catch(e) { servers = []; }
    }

    function routePage() {
      const path = window.location.pathname;
      if (path.startsWith('/server/')) {
        const parts = path.split('/');
        const id = parts[2];
        currentServer = id;
        showServerView(id);
      } else if (path === '/settings') {
        showSettings();
      } else if (path === '/api-keys') {
        showApiKeys();
      } else if (path === '/license') {
        showLicense();
      } else {
        showServersList();
      }
    }

    function navigate(e, path) {
      if (e) e.preventDefault();
      window.history.pushState({}, '', path);
      routePage();
    }

    // ── Servers List ──
    function showServersList() {
      currentServer = null;
      document.getElementById('nav-main').classList.remove('hidden');
      document.getElementById('nav-server').classList.add('hidden');
      document.getElementById('breadcrumb-text').textContent = 'Your Servers';
      setActiveNav('servers');

      const content = document.getElementById('content');
      const waitForServers = () => {
        if (servers.length === 0 && !document.querySelector('.server-card')) {
          setTimeout(() => { loadServers().then(() => renderServersList()); }, 200);
        } else {
          renderServersList();
        }
      };
      waitForServers();
    }

    function renderServersList() {
      const content = document.getElementById('content');
      const isGrid = currentView === 'grid';

      content.innerHTML = \`
        <div class="flex items-center justify-between mb-8 fade-up">
          <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Your Servers</h1>
            <p class="text-gray-500 text-sm mt-1">\${servers.length} servers registered</p>
          </div>
          <button class="bg-gradient-to-r from-void-600 to-void-700 hover:from-void-500 hover:to-void-600 text-white font-medium px-5 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-void-600/20 flex items-center gap-2">
            <i class="fas fa-plus"></i> New Server
          </button>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
          <div class="stat-card bg-dark-100/60 border border-white/5 rounded-xl p-4 fade-up" style="animation-delay:0.05s">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Total Servers</p>
                <p class="text-2xl font-bold text-white mt-1">\${servers.length}</p>
              </div>
              <div class="w-10 h-10 bg-void-600/10 rounded-xl flex items-center justify-center">
                <i class="fas fa-server text-void-500"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-dark-100/60 border border-white/5 rounded-xl p-4 fade-up" style="animation-delay:0.1s">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Online</p>
                <p class="text-2xl font-bold text-green-400 mt-1">\${servers.filter(s=>s.status==='online').length}</p>
              </div>
              <div class="w-10 h-10 bg-green-500/10 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-green-500"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-dark-100/60 border border-white/5 rounded-xl p-4 fade-up" style="animation-delay:0.15s">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Avg CPU</p>
                <p class="text-2xl font-bold text-white mt-1">\${(servers.reduce((a,s)=>a+s.cpu,0)/Math.max(servers.filter(s=>s.status==='online').length,1)).toFixed(1)}%</p>
              </div>
              <div class="w-10 h-10 bg-blue-500/10 rounded-xl flex items-center justify-center">
                <i class="fas fa-microchip text-blue-500"></i>
              </div>
            </div>
          </div>
          <div class="stat-card bg-dark-100/60 border border-white/5 rounded-xl p-4 fade-up" style="animation-delay:0.2s">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Total RAM</p>
                <p class="text-2xl font-bold text-white mt-1">3.9 GB</p>
              </div>
              <div class="w-10 h-10 bg-purple-500/10 rounded-xl flex items-center justify-center">
                <i class="fas fa-memory text-purple-500"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Server List/Grid -->
        <div class="\${isGrid ? 'grid grid-cols-1 md:grid-cols-2 gap-4' : 'space-y-3'}">
          \${servers.map((s, i) => isGrid ? gridCard(s, i) : listCard(s, i)).join('')}
        </div>
      \`;
    }

    function listCard(s, i) {
      return \`
        <div class="server-card bg-dark-100/60 border border-white/5 rounded-xl p-4 cursor-pointer fade-up flex items-center justify-between" style="animation-delay:\${i*0.05}s" onclick="navigate(null,'/server/\${s.id}')">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center \${s.status==='online'?'bg-green-500/10':'bg-dark-300/60'}">
              <i class="fas \${s.type==='Minecraft'?'fa-cube':'fa-code'} \${s.status==='online'?'text-green-400':'text-gray-500'}"></i>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-semibold text-white">\${s.name}</h3>
                <span class="w-2 h-2 rounded-full \${s.status==='online'?'bg-green-400':'s.status'==='installing'?'bg-yellow-400':'bg-red-400'}"></span>
              </div>
              <p class="text-xs text-gray-500 mt-0.5">\${s.address}</p>
            </div>
          </div>
          <div class="flex items-center gap-6 text-xs">
            \${s.status==='online' ? \`
              <div class="text-right"><span class="text-gray-500">CPU</span> <span class="text-white font-medium ml-1">\${s.cpu}%</span></div>
              <div class="text-right"><span class="text-gray-500">RAM</span> <span class="text-white font-medium ml-1">\${s.ram}</span></div>
              <div class="text-right"><span class="text-gray-500">Storage</span> <span class="text-white font-medium ml-1">\${s.storage}</span></div>
            \` : \`
              <span class="badge-\${s.status} px-3 py-1 rounded-lg text-xs font-medium capitalize">\${s.status}</span>
            \`}
          </div>
        </div>
      \`;
    }

    function gridCard(s, i) {
      return \`
        <div class="server-card bg-dark-100/60 border border-white/5 rounded-xl p-5 cursor-pointer fade-up" style="animation-delay:\${i*0.05}s" onclick="navigate(null,'/server/\${s.id}')">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center \${s.status==='online'?'bg-green-500/10':'bg-dark-300/60'}">
                <i class="fas \${s.type==='Minecraft'?'fa-cube':'fa-code'} \${s.status==='online'?'text-green-400':'text-gray-500'}"></i>
              </div>
              <div>
                <h3 class="font-semibold text-white">\${s.name}</h3>
                <p class="text-[11px] text-gray-500">\${s.address}</p>
              </div>
            </div>
            <span class="badge-\${s.status} px-2.5 py-1 rounded-lg text-[10px] font-medium capitalize">\${s.status}</span>
          </div>
          \${s.status==='online' ? \`
          <div class="grid grid-cols-3 gap-3">
            <div class="bg-dark-300/40 rounded-lg p-2.5 text-center">
              <p class="text-[10px] text-gray-500 uppercase">CPU</p>
              <p class="text-sm font-bold text-white">\${s.cpu}%</p>
            </div>
            <div class="bg-dark-300/40 rounded-lg p-2.5 text-center">
              <p class="text-[10px] text-gray-500 uppercase">RAM</p>
              <p class="text-sm font-bold text-white">\${s.ram}</p>
            </div>
            <div class="bg-dark-300/40 rounded-lg p-2.5 text-center">
              <p class="text-[10px] text-gray-500 uppercase">Disk</p>
              <p class="text-sm font-bold text-white">\${s.storage}</p>
            </div>
          </div>
          <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/5 text-xs text-gray-500">
            <span><i class="fas fa-clock mr-1"></i> \${s.uptime}</span>
            <span><i class="fas fa-users mr-1"></i> \${s.players}</span>
          </div>
          \` : \`<div class="text-center py-4 text-gray-500 text-sm">Server is \${s.status}</div>\`}
        </div>
      \`;
    }

    // ── Server Detail View ──
    async function showServerView(id) {
      document.getElementById('nav-main').classList.add('hidden');
      document.getElementById('nav-server').classList.remove('hidden');
      
      if (servers.length === 0) await loadServers();
      const server = servers.find(s => s.id === id);
      if (!server) { navigate(null, '/'); return; }
      
      document.getElementById('server-nav-name').innerHTML = \`
        <h3 class="font-bold text-white text-sm">\${server.name}</h3>
        <p class="text-[11px] text-gray-500">\${server.address}</p>
      \`;
      document.getElementById('breadcrumb-text').innerHTML = \`<a href="/" onclick="navigate(event,'/')" class="text-gray-500 hover:text-white transition-colors">Servers</a> <span class="text-gray-600 mx-1">/</span> \${server.name}\`;

      showServerPage(null, currentServerPage);
    }

    function showServerPage(e, page) {
      if (e) e.preventDefault();
      currentServerPage = page;
      
      // Update sidebar active state
      document.querySelectorAll('#nav-server .sidebar-item').forEach(el => {
        el.classList.remove('active');
        if (el.dataset.page === page) el.classList.add('active');
      });

      const server = servers.find(s => s.id === currentServer);
      if (!server) return;

      switch(page) {
        case 'console': renderConsole(server); break;
        case 'files': renderFiles(server); break;
        case 'databases': renderDatabases(server); break;
        case 'backups': renderBackups(server); break;
        case 'networking': renderNetworking(server); break;
        case 'server-settings': renderServerSettings(server); break;
        default: renderFiles(server);
      }
    }

    // ── Console Page ──
    function renderConsole(server) {
      const content = document.getElementById('content');
      content.innerHTML = \`
        <div class="fade-up">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">\${server.name}</h2>
            <div class="flex gap-2">
              <button class="bg-green-600/20 text-green-400 border border-green-600/30 hover:bg-green-600/30 px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-2">
                <i class="fas fa-play"></i> Start
              </button>
              <button class="bg-void-600/20 text-void-400 border border-void-600/30 hover:bg-void-600/30 px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-2">
                <i class="fas fa-redo"></i> Restart
              </button>
              <button class="bg-dark-200/60 text-gray-400 border border-white/5 hover:bg-dark-100/60 px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-2">
                <i class="fas fa-stop"></i> Stop
              </button>
            </div>
          </div>

          <!-- Resource Gauges -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-dark-100/60 border border-white/5 rounded-xl p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 uppercase font-medium">CPU Usage</span>
                <span class="text-sm font-bold text-white">\${server.cpu}%</span>
              </div>
              <div class="w-full bg-dark-300/60 rounded-full h-2">
                <div class="bg-gradient-to-r from-void-600 to-void-500 h-2 rounded-full transition-all" style="width:\${server.cpu}%"></div>
              </div>
            </div>
            <div class="bg-dark-100/60 border border-white/5 rounded-xl p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 uppercase font-medium">Memory</span>
                <span class="text-sm font-bold text-white">\${server.ram}</span>
              </div>
              <div class="w-full bg-dark-300/60 rounded-full h-2">
                <div class="bg-gradient-to-r from-blue-600 to-blue-400 h-2 rounded-full" style="width:42%"></div>
              </div>
            </div>
            <div class="bg-dark-100/60 border border-white/5 rounded-xl p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 uppercase font-medium">Disk</span>
                <span class="text-sm font-bold text-white">\${server.storage}</span>
              </div>
              <div class="w-full bg-dark-300/60 rounded-full h-2">
                <div class="bg-gradient-to-r from-purple-600 to-purple-400 h-2 rounded-full" style="width:38%"></div>
              </div>
            </div>
          </div>

          <!-- Console -->
          <div class="bg-dark-100/60 border border-white/5 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
              <h3 class="text-sm font-semibold text-white flex items-center gap-2"><i class="fas fa-terminal text-void-500"></i> Console</h3>
              <button class="text-xs text-gray-500 hover:text-white transition-colors"><i class="fas fa-expand"></i></button>
            </div>
            <div class="bg-dark-950 p-4 h-80 overflow-y-auto console-text text-xs leading-relaxed">
              <p class="text-gray-500">[00:00:01] <span class="text-blue-400">INFO</span>: Starting server on \${server.address}</p>
              <p class="text-gray-500">[00:00:02] <span class="text-blue-400">INFO</span>: Loading properties...</p>
              <p class="text-gray-500">[00:00:02] <span class="text-blue-400">INFO</span>: Default game type: SURVIVAL</p>
              <p class="text-gray-500">[00:00:03] <span class="text-green-400">INFO</span>: Preparing level "world"</p>
              <p class="text-gray-500">[00:00:04] <span class="text-green-400">INFO</span>: Preparing start region for dimension minecraft:overworld</p>
              <p class="text-gray-500">[00:00:08] <span class="text-green-400">INFO</span>: Time elapsed: 5247ms</p>
              <p class="text-gray-500">[00:00:08] <span class="text-green-400">INFO</span>: Done! Server started.</p>
              <p class="text-gray-500">[00:01:23] <span class="text-yellow-400">INFO</span>: Player joined the game</p>
              <p class="text-gray-500">[00:05:47] <span class="text-blue-400">INFO</span>: Saving chunks for world</p>
              <p class="text-gray-500">[00:10:00] <span class="text-blue-400">INFO</span>: Auto-save complete</p>
            </div>
            <div class="flex items-center border-t border-white/5">
              <span class="text-gray-500 pl-4 text-sm">$</span>
              <input type="text" placeholder="Type a command..." class="flex-1 bg-transparent border-none text-white px-3 py-3 text-sm focus:outline-none console-text placeholder-gray-600">
              <button class="px-4 py-3 text-void-500 hover:text-void-400 transition-colors"><i class="fas fa-paper-plane"></i></button>
            </div>
          </div>
        </div>
      \`;
    }

    // ── Files Page ──
    async function renderFiles(server) {
      let files = [];
      try { const r = await fetch('/api/servers/' + server.id + '/files'); files = await r.json(); } catch(e) {}
      
      const content = document.getElementById('content');
      content.innerHTML = \`
        <div class="fade-up">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-2xl font-bold text-white">\${server.name}</h2>
              <p class="text-xs text-gray-500 mt-1"><span class="text-gray-400">home</span> <i class="fas fa-chevron-right text-[8px] mx-1"></i> <span class="text-gray-400">container</span></p>
            </div>
            <div class="flex gap-2">
              <button class="bg-dark-100/60 border border-white/5 hover:bg-dark-50/60 hover:border-white/10 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-2">
                <i class="fas fa-file-alt text-void-500"></i> New File
              </button>
              <button class="bg-dark-100/60 border border-white/5 hover:bg-dark-50/60 hover:border-white/10 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-2">
                <i class="fas fa-folder-plus text-void-500"></i> New Folder
              </button>
              <button class="bg-gradient-to-r from-void-600 to-void-700 hover:from-void-500 hover:to-void-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all shadow-lg shadow-void-600/20 flex items-center gap-2">
                <i class="fas fa-upload"></i> Upload
              </button>
            </div>
          </div>

          <div class="bg-dark-100/60 border border-white/5 rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="grid grid-cols-12 gap-4 px-4 py-3 border-b border-white/5 text-xs text-gray-500 uppercase tracking-wider font-medium">
              <div class="col-span-1"><input type="checkbox" class="rounded border-white/10 bg-dark-300 text-void-600 focus:ring-void-600 w-4 h-4"></div>
              <div class="col-span-6">Name</div>
              <div class="col-span-2">Size</div>
              <div class="col-span-3">Modified</div>
            </div>
            <!-- Files -->
            \${files.map((f, i) => \`
              <div class="file-row grid grid-cols-12 gap-4 px-4 py-3 border-b border-white/5 items-center cursor-pointer animate-in" style="animation-delay:\${i*0.02}s" onclick="this.classList.toggle('selected')">
                <div class="col-span-1"><input type="checkbox" class="rounded border-white/10 bg-dark-300 text-void-600 focus:ring-void-600 w-4 h-4"></div>
                <div class="col-span-6 flex items-center gap-3">
                  <i class="fas \${f.type==='folder'?'fa-folder text-void-500':'fa-file-code text-gray-500'} w-4 text-center"></i>
                  <span class="text-sm text-white font-medium">\${f.name}</span>
                </div>
                <div class="col-span-2 text-xs text-gray-500">\${f.size}</div>
                <div class="col-span-3 text-xs text-gray-500">\${f.date}</div>
              </div>
            \`).join('')}
          </div>
        </div>
      \`;
    }

    // ── Databases Page ──
    function renderDatabases(server) {
      document.getElementById('content').innerHTML = \`
        <div class="fade-up">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Databases</h2>
            <button class="bg-gradient-to-r from-void-600 to-void-700 hover:from-void-500 hover:to-void-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all shadow-lg shadow-void-600/20 flex items-center gap-2">
              <i class="fas fa-plus"></i> New Database
            </button>
          </div>
          <div class="bg-dark-100/60 border border-white/5 rounded-xl p-12 text-center">
            <i class="fas fa-database text-4xl text-gray-600 mb-4"></i>
            <p class="text-gray-400 text-lg font-medium">No databases configured</p>
            <p class="text-gray-600 text-sm mt-1">Create a new database to get started</p>
          </div>
        </div>
      \`;
    }

    // ── Backups Page ──
    function renderBackups(server) {
      document.getElementById('content').innerHTML = \`
        <div class="fade-up">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Backups</h2>
            <button class="bg-gradient-to-r from-void-600 to-void-700 hover:from-void-500 hover:to-void-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all shadow-lg shadow-void-600/20 flex items-center gap-2">
              <i class="fas fa-plus"></i> Create Backup
            </button>
          </div>
          <div class="space-y-3">
            <div class="bg-dark-100/60 border border-white/5 rounded-xl p-4 flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-green-500/10 rounded-xl flex items-center justify-center"><i class="fas fa-archive text-green-400"></i></div>
                <div>
                  <h3 class="text-sm font-semibold text-white">backup_2026-07-14.tar.gz</h3>
                  <p class="text-xs text-gray-500">Created 2 hours ago &bull; 1.4 GB</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button class="text-gray-500 hover:text-white p-2 transition-colors"><i class="fas fa-download"></i></button>
                <button class="text-gray-500 hover:text-void-500 p-2 transition-colors"><i class="fas fa-trash"></i></button>
              </div>
            </div>
            <div class="bg-dark-100/60 border border-white/5 rounded-xl p-4 flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-green-500/10 rounded-xl flex items-center justify-center"><i class="fas fa-archive text-green-400"></i></div>
                <div>
                  <h3 class="text-sm font-semibold text-white">backup_2026-07-13.tar.gz</h3>
                  <p class="text-xs text-gray-500">Created yesterday &bull; 1.3 GB</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button class="text-gray-500 hover:text-white p-2 transition-colors"><i class="fas fa-download"></i></button>
                <button class="text-gray-500 hover:text-void-500 p-2 transition-colors"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>
      \`;
    }

    // ── Networking Page ──
    function renderNetworking(server) {
      document.getElementById('content').innerHTML = \`
        <div class="fade-up">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Networking</h2>
            <button class="bg-gradient-to-r from-void-600 to-void-700 hover:from-void-500 hover:to-void-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all shadow-lg shadow-void-600/20 flex items-center gap-2">
              <i class="fas fa-plus"></i> Add Allocation
            </button>
          </div>
          <div class="bg-dark-100/60 border border-white/5 rounded-xl overflow-hidden">
            <div class="grid grid-cols-4 gap-4 px-4 py-3 border-b border-white/5 text-xs text-gray-500 uppercase tracking-wider font-medium">
              <div>Address</div><div>Port</div><div>Alias</div><div>Primary</div>
            </div>
            <div class="grid grid-cols-4 gap-4 px-4 py-3 items-center border-b border-white/5">
              <div class="text-sm text-white">\${server.address.split(':')[0]}</div>
              <div class="text-sm text-white">\${server.address.split(':')[1]}</div>
              <div class="text-sm text-gray-500">-</div>
              <div><span class="badge-online px-2 py-0.5 rounded text-xs">Primary</span></div>
            </div>
          </div>
        </div>
      \`;
    }

    // ── Server Settings ──
    function renderServerSettings(server) {
      document.getElementById('content').innerHTML = \`
        <div class="fade-up">
          <h2 class="text-2xl font-bold text-white mb-6">Server Settings</h2>
          <div class="space-y-6">
            <div class="bg-dark-100/60 border border-white/5 rounded-xl p-6">
              <h3 class="text-lg font-semibold text-white mb-4">General</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Server Name</label>
                  <input type="text" value="\${server.name}" class="w-full bg-dark-300/60 border border-white/5 rounded-xl px-4 py-2.5 text-white text-sm focus:border-void-600 focus:outline-none transition-all">
                </div>
                <div>
                  <label class="block text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Description</label>
                  <input type="text" placeholder="Add description..." class="w-full bg-dark-300/60 border border-white/5 rounded-xl px-4 py-2.5 text-white text-sm focus:border-void-600 focus:outline-none transition-all placeholder-gray-600">
                </div>
              </div>
            </div>
            <div class="bg-void-950/40 border border-void-800/30 rounded-xl p-6">
              <h3 class="text-lg font-semibold text-void-400 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Danger Zone</h3>
              <p class="text-sm text-gray-500 mb-4">These actions are irreversible. Please be certain.</p>
              <div class="flex gap-3">
                <button class="bg-void-600/20 text-void-400 border border-void-600/30 hover:bg-void-600/30 px-4 py-2 rounded-xl text-sm font-medium transition-all">Reinstall Server</button>
                <button class="bg-void-800/30 text-void-300 border border-void-700/30 hover:bg-void-700/30 px-4 py-2 rounded-xl text-sm font-medium transition-all">Delete Server</button>
              </div>
            </div>
          </div>
        </div>
      \`;
    }

    // ── Settings Page ──
    function showSettings() {
      currentServer = null;
      document.getElementById('nav-main').classList.remove('hidden');
      document.getElementById('nav-server').classList.add('hidden');
      document.getElementById('breadcrumb-text').textContent = 'Settings';
      setActiveNav('settings');

      document.getElementById('content').innerHTML = \`
        <div class="fade-up">
          <h1 class="text-3xl font-extrabold text-white tracking-tight mb-8">Settings</h1>
          <div class="space-y-6">
            <div class="bg-dark-100/60 border border-white/5 rounded-xl p-6">
              <h3 class="text-lg font-semibold text-white mb-4">Profile</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Username</label>
                  <input type="text" value="\${user.username}" class="w-full bg-dark-300/60 border border-white/5 rounded-xl px-4 py-2.5 text-white text-sm focus:border-void-600 focus:outline-none">
                </div>
                <div>
                  <label class="block text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Email</label>
                  <input type="text" value="\${user.email}" class="w-full bg-dark-300/60 border border-white/5 rounded-xl px-4 py-2.5 text-white text-sm focus:border-void-600 focus:outline-none">
                </div>
              </div>
              <button class="mt-4 bg-gradient-to-r from-void-600 to-void-700 hover:from-void-500 hover:to-void-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all shadow-lg shadow-void-600/20">Save Changes</button>
            </div>
            <div class="bg-dark-100/60 border border-white/5 rounded-xl p-6">
              <h3 class="text-lg font-semibold text-white mb-4">Appearance</h3>
              <p class="text-sm text-gray-500 mb-3">Choose your preferred color scheme</p>
              <div class="flex gap-3">
                <button class="w-8 h-8 rounded-full bg-void-600 ring-2 ring-void-400 ring-offset-2 ring-offset-dark-950"></button>
                <button class="w-8 h-8 rounded-full bg-blue-600 ring-2 ring-transparent hover:ring-blue-400 ring-offset-2 ring-offset-dark-950 transition-all"></button>
                <button class="w-8 h-8 rounded-full bg-green-600 ring-2 ring-transparent hover:ring-green-400 ring-offset-2 ring-offset-dark-950 transition-all"></button>
                <button class="w-8 h-8 rounded-full bg-purple-600 ring-2 ring-transparent hover:ring-purple-400 ring-offset-2 ring-offset-dark-950 transition-all"></button>
                <button class="w-8 h-8 rounded-full bg-orange-600 ring-2 ring-transparent hover:ring-orange-400 ring-offset-2 ring-offset-dark-950 transition-all"></button>
              </div>
            </div>
          </div>
        </div>
      \`;
    }

    // ── API Keys Page ──
    function showApiKeys() {
      currentServer = null;
      document.getElementById('nav-main').classList.remove('hidden');
      document.getElementById('nav-server').classList.add('hidden');
      document.getElementById('breadcrumb-text').textContent = 'API Keys';
      setActiveNav('api-keys');

      document.getElementById('content').innerHTML = \`
        <div class="fade-up">
          <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-extrabold text-white tracking-tight">API Keys</h1>
            <button class="bg-gradient-to-r from-void-600 to-void-700 hover:from-void-500 hover:to-void-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all shadow-lg shadow-void-600/20 flex items-center gap-2">
              <i class="fas fa-plus"></i> Create Key
            </button>
          </div>
          <div class="bg-dark-100/60 border border-white/5 rounded-xl p-4">
            <div class="flex items-center justify-between p-3 rounded-lg hover:bg-white/3 transition-all">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-yellow-500/10 rounded-xl flex items-center justify-center"><i class="fas fa-key text-yellow-500"></i></div>
                <div>
                  <h3 class="text-sm font-semibold text-white">Production API Key</h3>
                  <p class="text-xs text-gray-500 font-mono">void_pk_••••••••••••7f3a</p>
                </div>
              </div>
              <div class="flex items-center gap-4 text-xs text-gray-500">
                <span>Created Jul 10, 2026</span>
                <button class="text-void-500 hover:text-void-400 transition-colors"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>
      \`;
    }

    // ── License Page ──
    function showLicense() {
      currentServer = null;
      document.getElementById('nav-main').classList.remove('hidden');
      document.getElementById('nav-server').classList.add('hidden');
      document.getElementById('breadcrumb-text').textContent = 'License';

      document.getElementById('content').innerHTML = \`
        <div class="fade-up max-w-3xl">
          <h1 class="text-3xl font-extrabold text-white tracking-tight mb-2">License</h1>
          <p class="text-gray-500 mb-8">VoidPanel Software License Agreement</p>
          
          <div class="bg-dark-100/60 border border-white/5 rounded-xl p-8 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-white mb-2">MIT License</h3>
              <p class="text-sm text-gray-400 leading-relaxed">Copyright (c) 2024-2026 Void Development</p>
            </div>
            <div class="text-sm text-gray-400 leading-relaxed space-y-4">
              <p>Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:</p>
              <p>The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.</p>
              <p>THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</p>
            </div>
            <div class="pt-4 border-t border-white/5">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-void-600 to-void-800 rounded-xl flex items-center justify-center">
                  <i class="fas fa-dragon text-white"></i>
                </div>
                <div>
                  <p class="text-sm font-semibold text-white">Void Development</p>
                  <p class="text-xs text-gray-500">Building the future of game server management</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      \`;
    }

    // ── Utilities ──
    function setActiveNav(page) {
      document.querySelectorAll('#nav-main .sidebar-item').forEach(el => {
        el.classList.remove('active');
        if (el.dataset.page === page) el.classList.add('active');
      });
    }

    function setView(v) {
      currentView = v;
      document.getElementById('view-list').classList.toggle('bg-white/5', v==='list');
      document.getElementById('view-grid').classList.toggle('bg-white/5', v==='grid');
      if (!currentServer) renderServersList();
    }

    function toggleTheme() {}

    function logout() {
      localStorage.removeItem('void_user');
      window.location.href = '/login';
    }

    window.addEventListener('popstate', routePage);
  </script>
</body>
</html>`
}

export default app
