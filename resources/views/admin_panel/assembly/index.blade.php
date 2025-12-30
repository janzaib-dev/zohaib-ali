@extends('admin_panel.layout.app')
@section('content')
<div class="container mt-5">

<h5 class="mb-3">Assembly Report</h5>
<div class="table-responsive">
  <table class="table table-bordered table-sm">
    <thead class="table-light">
      <tr>
        <th>Product</th>
        <th>Ready stock</th>
        {{-- <th>Assemble possible</th> --}}
        <th>Total sellable</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td>{{ $r['product_name'] }}</td>
          <td>{{ $r['ready_stock'] }}</td>
          {{-- <td>{{ $r['assemble_possible'] }}</td> --}}
          <td>{{ $r['total_sellable'] }}</td>
          <td>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('assembly.report.show',$r['product_id']) }}">
              View
            </a>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
</div>

@endsection
