<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Country</th>
            <th>Phone</th>
            <th>Spent Amount</th>
            <th>Address</th>
            <th>Username</th>
            <th>Status</th>
            <th>Last Active</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($travelers as $traveler)
            <tr>
                <td>{{ $traveler->id }}</td>
                <td>{{ $traveler->name ?? 'N/A' }}</td>
                <td>{{ $traveler->email ?? 'N/A' }}</td>
                <td>{{ $traveler->country ?? 'N/A' }}</td>
                <td>{{ $traveler->phone ?? 'N/A' }}</td>
                <td>{{ $traveler->spent_amount ? number_format($traveler->spent_amount, 2) : '0.00' }}</td>
                <td>{{ $traveler->address ?? 'N/A' }}</td>
                <td>{{ $traveler->username ?? 'N/A' }}</td>
                <td>{{ $traveler->status ?? 'N/A' }}</td>
                <td>{{ $traveler->last_active ? $traveler->last_active->format('Y-m-d H:i:s') : 'N/A' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
