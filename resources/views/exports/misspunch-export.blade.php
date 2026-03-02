<table>
    <tr>
      <th>Name</th>
      <th>Date</th>
      <th>Type</th>
      <th>In Time</th>
      <th>Out Time</th>
      <th>Description</th>
      <th>Requested at</th>
      <th>status</th>
      <th>action By</th>
      <th>action at</th>
      <th>Remark</th>




    </tr>

    <tbody>

        @foreach($data as $item)


        <tr>
          <td>{{ $item->name }}</td>
          <td>{{ $item->date }}</td>
          <td>{{ ($item->type == 1 ? 'Checkin' : ($item->type == 2 ? 'Checkout' : 'Checkin & Checkout'))}}</td>
          <td>{{ $item->checkinTime }}</td>
          <td>{{ $item->checkoutTime }}</td>

          <td>{{ $item->description }}</td>
          <td>{{ $item->requested_at }}</td>
          <td>{{ ($item->status == 0 ? 'Pending' : ($item->status == 1 ? 'Approved' : 'Rejected'))}}</td>
          <td>{{ $item->action_by_name }}</td>
          <td>{{ $item->action_at }}</td>
          <td>{{ $item->remark }}</td>




        </tr>
        @endforeach

    </tbody>
</table>
