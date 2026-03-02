<table>
  <tr>
      <th>Name</th>
      <th>Date</th>
      <th>Checkin</th>
      <th>Checkout</th>


  </tr>

  <tbody>

      @foreach($data as $item)


      <tr>
          <td>{{ $item->name }}</td>
          <td>{{ $item->date }}</td>
          <td>{{ $item->InTime }}</td>
          <td>@if($item->InTime != $item->OutTime){{ $item->OutTime }} @else No Records @endif</td>





      </tr>
      @endforeach

  </tbody>
</table>
