<table>
    <tr>
        <th>Name</th>
        <th>Leave Type</th>
        <th>Date</th>
        <th>Leave Day Type</th>

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
            <td>{{ $item->leave_type }}</td>
            <td>{{ $item->date }}</td>
            <td>{{ ($item->leave_day_type == 1 ? "Full Day" : ($item->leave_day_type == 2 ? "FN" : "AN")) }}</td>

            <td>{{ $item->requested_at }}</td>
            <td>{{ ($item->status == 0 ? 'Pending' : ($item->status == 1 ? 'Approved' : 'Rejected'))}}</td>
            <td>{{ $item->action_by_name }}</td>
            <td>{{ $item->action_at }}</td>
            <td>{{ $item->remark }}</td>




        </tr>
        @endforeach

    </tbody>
</table>
