<ul id="myUL">
    @foreach($categorias->where('padre', $parent) as $categoria)
        <li>
            <span class="caret">{{ $categoria->categoria }}</span>
            <div class="btn-group btn_tree">
                <button type="button" onclick="editarTree({{ $categoria->id }}, [], {{ $level }})" class="btn btn-primary btn-xs"><i class="fas fa-edit" aria-hidden="true"></i></button>
                <button type="button" onclick="delTree({{ $categoria->id }}, [], {{ $level }})" class="btn btn-danger btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>
                <button type="button" onclick="addTree({{ $categoria->id }}, [], {{ $level }})" class="btn btn-success btn-xs"><i class="fa fa-plus" aria-hidden="true"></i></button>
            </div>
            @if($categorias->where('padre', $categoria->id)->count())
                <ul class="nested">
                    @include('partials.categorias_tree', ['categorias' => $categorias, 'parent' => $categoria->id, 'level' => $level + 1])
                </ul>
            @endif
        </li>
    @endforeach
</ul>
