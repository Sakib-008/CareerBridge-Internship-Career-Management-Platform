<a href="{{ route('admin.dashboard') }}"
   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2 me-2"></i> Dashboard
</a>
<a href="#"
   class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
    <i class="bi bi-people-fill me-2"></i> Manage Users
</a>
<a href="#"
   class="nav-link {{ request()->routeIs('admin.internships*') ? 'active' : '' }}">
    <i class="bi bi-briefcase-fill me-2"></i> All Internships
</a>
<a href="#"
   class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
    <i class="bi bi-bar-chart-fill me-2"></i> Reports
</a>
<a href="#"
   class="nav-link {{ request()->routeIs('admin.skills*') ? 'active' : '' }}">
    <i class="bi bi-tags-fill me-2"></i> Skill Catalog
</a>