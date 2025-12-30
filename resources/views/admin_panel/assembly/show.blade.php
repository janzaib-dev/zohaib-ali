@extends('admin_panel.layout.app')
@section('content')
<div class="container-fluid px-2 px-md-3">

  <style>
    .page-card{border:1px solid #e5e7eb;border-radius:12px;background:#fff;box-shadow:0 2px 6px -2px rgba(16,24,40,.08)}
    .page-head{padding:12px 14px;border-bottom:1px solid #e5e7eb;display:flex;gap:10px;align-items:center;justify-content:space-between}
    .page-body{padding:14px}
    .soft{color:#6b7280}
    .stat{padding:6px 8px;border:1px solid #e5e7eb;border-radius:10px;background:#fafafa}
    .stat-title{font-size:.8rem;color:#6b7280}
    .stat-value{font-size:1rem;font-weight:600}
    .stat-green{color:#059669}
    .stat-blue{color:#2563eb}
    .stat-purple{color:#6d28d9}
    .chip{display:inline-block;padding:2px 8px;font-size:.8rem;font-weight:600;border-radius:20px}
    .chip-ok{background:#ecfdf5;color:#065f46}
    .chip-warn{background:#fef2f2;color:#b91c1c}
    .short-row{background:#fff7ed}
    .mini-note{font-size:.75rem;color:#6b7280}
  </style>

  <div class="page-card">

    {{-- Header --}}
    <div class="page-head">
      <h6 class="mb-0">Assembly Report — <span class="text-muted">{{ $product->item_name }}</span></h6>
      <div>
        <a href="{{ route('assembly.report') }}" class="btn btn-sm btn-outline-secondary">Back</a>
      </div>
    </div>

    <div class="page-body">

      {{-- Flash --}}
      @if(session('success'))
        <div class="alert alert-success py-2 px-3 small mb-2">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger py-2 px-3 small mb-2">{{ session('error') }}</div>
      @endif

      {{-- Stats --}}
      <div class="row g-2 mb-2">
        <div class="col-md-4">
          <div class="stat">
            <div class="stat-title">Ready stock</div>
            <div class="stat-value stat-green">{{ $snap['ready_stock'] }}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat">
            <div class="stat-title">Assemblable remaining (now)</div>
            <div class="stat-value stat-blue">{{ $snap['assemble_possible'] }}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat">
            <div class="stat-title">Total sellable</div>
            <div class="stat-value stat-purple">{{ $snap['total_sellable'] }}</div>
          </div>
        </div>
      </div>

      {{-- “Next unit” shortage helper --}}
      @if(count($snap['shortages_for_next']))
        <div class="alert alert-warning py-2 px-3 mt-2 mb-2 small">
          @if(count($snap['shortages_for_next'])===1 && (float)($snap['shortages_for_next'][0]['shortage'])===1.0)
            <b>Almost there!</b> Bas <b>1</b> x {{ $snap['shortages_for_next'][0]['name'] }} chahiye — itna aate hi <b>1</b> aur unit ready ho jayegi.
          @else
            <b>Next unit shortages:</b>
            @foreach($snap['shortages_for_next'] as $s)
              <span class="badge bg-warning text-dark">{{ $s['name'] }} ({{ rtrim(rtrim(number_format($s['shortage'],2,'.',''), '0'), '.') }})</span>
            @endforeach
          @endif
        </div>
      @endif

      {{-- Parts table --}}
      <h6 class="mt-2 mb-2">Parts</h6>
      <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
          <thead class="table-light">
            <tr>
              <th>Part</th>
              <th>Code</th>
              <th class="text-center">Required / Unit</th>
              <th class="text-center">Available (Part)</th>
              <th class="text-center">Max from FG</th>
              <th class="text-center">Sellable (auto)</th>
            </tr>
          </thead>
          <tbody>
            {{-- @php
              
              echo($snap['parts']);
            @endphp --}}
              @foreach($snap['parts'] as $p)

                @php
                  $rowShort   = $p->qty_per_unit > $p->available_qty;
                  // echo "<br>";
                  // echo($rowShort);
                  echo "<br>";
                  // dd($snap['pluckable_from_fg']);
                  // echo "<pre>";
                    // print_r($snap['pluckable_from_fg'][$p->part_id]);
                    // echo "<br>";
                    // dd();
                  //   echo "sellable";
                  //   print_r($snap['sellable_parts_now'][$p->part_id]);
                  //   echo "<pre>";
                  $maxPluck   = (float) ($snap['pluckable_from_fg'][$p->part_id] ?? 0);
                  $sellable   = (float) ($snap['sellable_parts_now'][$p->part_id] ?? ((float)$p->available_qty + $maxPluck));
                  $maxPluckFmt = rtrim(rtrim(number_format($maxPluck, 4, '.', ''), '0'), '.');
                  $sellableFmt = rtrim(rtrim(number_format($sellable, 4, '.', ''), '0'), '.');
                @endphp
                <tr class="{{ $rowShort ? 'short-row' : '' }}">
                  <td>{{ $p->item_name }}</td>
                  <td class="text-muted">{{ $p->item_code }}</td>
                  <td class="text-center">{{ (float)$p->qty_per_unit }}</td>
                  <td class="text-center">
                    <span class="chip {{ $rowShort ? 'chip-warn' : 'chip-ok' }}">
                    {{ (float) ($p->available_qty > 0 ? $p->available_qty : 0) }}

                    </span>
                  </td>
                  <td class="text-center">
                    {{ $maxPluckFmt }}
                    <div class="mini-note">FG ready: {{ $snap['ready_stock'] ?? 0 }}</div>
                  </td>
                  <td class="text-center">
                    <strong>{{ $sellableFmt }}</strong>
                    <div class="mini-note">Auto (part + FG)</div>
                  </td>
                </tr>
              @endforeach
          </tbody>
        </table>
      </div>

      {{-- Shortages for 1 unit (current) --}}
      @if(count($snap['shortages_for_1']))
        <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
          <b>Short parts for 1 unit:</b>
          @foreach($snap['shortages_for_1'] as $s)
            <span class="badge bg-warning text-dark">{{ $s['name'] }} ({{ rtrim(rtrim(number_format($s['shortage'],2,'.',''), '0'), '.') }})</span>
          @endforeach
        </div>
      @endif
    </div>

  </div>
</div>
@endsection
