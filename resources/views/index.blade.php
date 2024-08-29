<h1>Projects</h1>
<ul>
    @foreach($projects as $project)
        <li>{{ $project->name }} by {{ $project->description }}</li>
    @endforeach
</ul>
