<table>
    <tr>
        <th>Name</th>
        <th>Date From</th>
        <th>Time</th>
        <th>Date To</th>
        <th>Time</th>
        <th>Title</th>
        <th>Type</th>
        <th>Location</th>
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
            <td>{{ $item->start_date }}</td>
            <td>{{ $item->start_time }}</td>
            <td>{{ $item->end_date }}</td>
            <td>{{ $item->end_time }}</td>
            <td>{{ $item->title }}</td>
            <td>{{ $item->type }}</td>
            <td>{{ $item->location }}</td>
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
