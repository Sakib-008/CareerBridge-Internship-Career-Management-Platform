<a href="{{ route('admin.dashboard') }}"
   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2 me-2"></i> Dashboard
</a>
<a href="{{ route('admin.users') }}"
   class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
    <i class="bi bi-people-fill me-2"></i> Manage Users
</a>

<div class="px-3 mt-3 mb-1">
    <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
        Reports
    </small>
</div>
<a href="{{ route('admin.reports.applications') }}"
   class="nav-link {{ request()->routeIs('admin.reports.applications') ? 'active' : '' }}">
    <i class="bi bi-bar-chart me-2"></i> Application Pipeline
</a>
<a href="{{ route('admin.reports.placement') }}"
   class="nav-link {{ request()->routeIs('admin.reports.placement') ? 'active' : '' }}">
    <i class="bi bi-mortarboard me-2"></i> Student Placement
</a>
<a href="{{ route('admin.reports.skills') }}"
   class="nav-link {{ request()->routeIs('admin.reports.skills') ? 'active' : '' }}">
    <i class="bi bi-tags me-2"></i> Skill Demand
</a>
<a href="{{ route('admin.reports.companies') }}"
   class="nav-link {{ request()->routeIs('admin.reports.companies') ? 'active' : '' }}">
    <i class="bi bi-building me-2"></i> Company Activity
</a>

<div class="px-3 mt-3 mb-1">
    <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
        Management
    </small>
</div>
<a href="{{ route('admin.skills') }}"
   class="nav-link {{ request()->routeIs('admin.skills*') ? 'active' : '' }}">
    <i class="bi bi-tags-fill me-2"></i> Skill Catalog
</a>