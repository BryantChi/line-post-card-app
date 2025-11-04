<div class='btn-group'>
    <a href="{{ $showUrl }}" class='btn btn-default btn-xs'>
        <i class="fa fa-eye"></i>
    </a>
    <a href="{{ $editUrl }}" class='btn btn-default btn-xs'>
        <i class="fa fa-edit"></i>
    </a>
    <a class='btn btn-danger btn-xs js-confirm-delete' wire:click="deleteRecord({{ $recordId }})"
       data-confirm="Are you sure you want to remove this Record?">
        <i class="fa fa-trash"></i>
    </a>
</div>
