@extends('admin_panel.layout.app')
@section('content')
<div class="container-fluid px-2 px-md-3">

  @if(session('success'))
    <div class="alert alert-success py-2 px-3">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger py-2 px-3">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h6 class="mb-0">Bulk Stock Adjust</h6>
      <a href="{{ route('product') }}" class="btn btn-sm btn-outline-secondary">Back to Products</a>
    </div>

    <form action="{{ route('assembly.adjust.bulk') }}" method="POST" id="adjustForm">
      @csrf
      <div class="card-body p-2 p-md-3">

        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
            <tr>
              <th style="min-width:300px">Part</th>
              <th class="text-center" style="min-width:120px">Available</th>
              <th class="text-center" style="min-width:120px">Adjust (+/−)</th>
              <th class="text-center" style="min-width:120px">New available</th>
              <th style="min-width:220px">Note (optional)</th>
              <th style="min-width:70px">Action</th>
            </tr>
            </thead>
            <tbody id="rows"></tbody>
          </table>
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm" id="addRow">
          <i class="las la-plus-circle"></i> Add Row
        </button>

      </div>

      <div class="card-footer d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary">Save adjustments</button>
      </div>
    </form>
  </div>
</div>

{{-- libs --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function(){
  let idx = 0;
  const $rows = $('#rows');

  function rowTemplate(i){
    return `
<tr data-index="${i}">
  <td>
    <select class="form-select part-select" name="items[${i}][part_id]" style="width:100%"></select>
    <div class="small text-muted">Search part by name/code</div>
  </td>
  <td class="text-center">
    <input type="number" class="form-control form-control-sm available" value="0" readonly>
  </td>
  <td class="text-center">
    <input type="number" name="items[${i}][delta]" class="form-control form-control-sm delta" value="0" step="1">
  </td>
  <td class="text-center">
    <input type="number" class="form-control form-control-sm new-available" value="0" readonly>
  </td>
  <td>
    <input type="text" name="items[${i}][note]" class="form-control form-control-sm" placeholder="e.g. physical count correction">
  </td>
  <td class="text-center">
    <button type="button" class="btn btn-sm btn-outline-danger del-row">X</button>
  </td>
</tr>`;
  }

  function initSelect2($sel){
    $sel.select2({
      placeholder: 'Search part...',
      width: '100%',
      ajax: {
        delay: 200,
        url: "{{ route('search-part-name') }}",
        dataType: 'json',
        data: params => ({ q: params.term || '' }),
        processResults: data => ({
          results: (data||[]).map(p => ({
            id: p.id,
            text: `${p.item_name} - ${p.item_code}`,
            available_qty: Number(p.available_qty || 0)
          }))
        })
      }
    });

    $sel.on('select2:select', function(e){
      const d = e.params.data;
      const $tr = $(this).closest('tr');
      const avail = Number(d.available_qty || 0);
      $tr.find('.available').val(avail);
      const delta = Number($tr.find('.delta').val() || 0);
      $tr.find('.new-available').val(avail + delta);
    });
  }

  function wireRow($tr){
    // delta change -> recompute
    $tr.on('input', '.delta', function(){
      const avail = Number($tr.find('.available').val() || 0);
      const delta = Number($(this).val() || 0);
      $tr.find('.new-available').val(avail + delta);
    });

    // delete
    $tr.on('click', '.del-row', function(){
      $tr.remove();
      if(!$rows.children().length) addRow();
    });

    initSelect2($tr.find('.part-select'));
  }

  function addRow(){
    const $tr = $(rowTemplate(idx++));
    $rows.append($tr);
    wireRow($tr);
  }

  // init with 1 row
  $('#addRow').on('click', addRow);
  addRow();
})();
</script>
@endsection
