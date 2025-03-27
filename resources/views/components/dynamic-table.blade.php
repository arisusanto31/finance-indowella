<div class="p-4">
    <h5 class="text-lg font-semibold mb-2">{{ $title }}</h5>
  
    <div style="max-height: 400px; overflow-x: auto; overflow-y: auto;">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            @foreach ($headers as $index => $col)
              <th class="{{ $index < 5 ? 'sticky-col-' . ($index + 1) : '' }} bg-primary text-white">
                {{ $col }}
              </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $row)
            <tr>
              @foreach ($row as $i => $val)
                <td class="{{ $i < 5 ? 'sticky-col-' . ($i + 1) : '' }}">{{ $val }}</td>
              @endforeach
            </tr>
          @endforeach
  
          @if (!empty($subtotal))
            <tr style="background-color: #f8f9fa; font-weight: bold;">
              @foreach ($subtotal as $i => $val)
                <td class="{{ $i < 5 ? 'sticky-col-' . ($i + 1) : '' }}">{{ $val }}</td>
              @endforeach
            </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
  