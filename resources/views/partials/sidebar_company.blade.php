<a href="{{ route('company.dashboard') }}"
   class="nav-link {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2 me-2"></i> Dashboard
</a>
<a href="{{ route('company.profile') }}"
   class="nav-link {{ request()->routeIs('company.profile*') ? 'active' : '' }}">
    <i class="bi bi-building me-2"></i> Company Profile
</a>
<a href="{{ route('company.internships') }}"
   class="nav-link {{ request()->routeIs('company.internships*') ? 'active' : '' }}">
    <i class="bi bi-briefcase me-2"></i> My Internships
</a>
<a href="{{ route('company.applications') }}"
   class="nav-link {{ request()->routeIs('company.applications*') ? 'active' : '' }}">
    <i class="bi bi-people me-2"></i> Applications
</a>
<a href="#"
   class="nav-link {{ request()->routeIs('company.interviews*') ? 'active' : '' }}">
    <i class="bi bi-calendar-check me-2"></i> Interviews
</a>