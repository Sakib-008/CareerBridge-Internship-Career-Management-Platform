<a href="{{ route('student.dashboard') }}"
   class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2 me-2"></i> Dashboard
</a>
<a href="{{ route('student.profile') }}"
   class="nav-link {{ request()->routeIs('student.profile*') ? 'active' : '' }}">
    <i class="bi bi-person-circle me-2"></i> My Profile
</a>
<a href="{{ route('student.skills') }}"
   class="nav-link {{ request()->routeIs('student.skills*') ? 'active' : '' }}">
    <i class="bi bi-stars me-2"></i> My Skills
</a>
<a href="#"
   class="nav-link {{ request()->routeIs('internships*') ? 'active' : '' }}">
    <i class="bi bi-search me-2"></i> Browse Internships
</a>
<a href="#"
   class="nav-link {{ request()->routeIs('student.applications*') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text me-2"></i> My Applications
</a>
<a href="#"
   class="nav-link {{ request()->routeIs('student.interviews*') ? 'active' : '' }}">
    <i class="bi bi-camera-video me-2"></i> Interviews
</a>
<a href="#"
   class="nav-link {{ request()->routeIs('student.recommendations*') ? 'active' : '' }}">
    <i class="bi bi-lightbulb me-2"></i> Recommendations
</a>