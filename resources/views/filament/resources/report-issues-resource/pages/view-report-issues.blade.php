<x-filament-panels::page>
    <table class="mt-4 w-full bg-black shadow rounded text-white table-auto">
        <tbody>
            <tr class="border justify-between">
                <th class=" p-4">Customer Name</th>
                <th class=" p-4">Engineer Name</th>
            </tr>
            <tr class="border">
                <td class="p-4 w-1/2 text-center">
                    <div>
                        <img class="h-10 w-14 rounded-full block mx-auto"
                            src="{{ asset($record->user->avatar ?? 'uploads/discussion_requests/1737881192.jpg') }}"
                            alt="Avatar">
                        <br>
                        {{ $record->user->name ?? 'N/A' }}
                        <br>
                        <span class="text-sm">Email: {{ $record->user->email ?? 'N/A' }}</span>
                        <br>
                        <span class="text-sm">Address: {{ $record->user->address ?? 'N/A' }}</span>
                        <br>
                        <span class="text-sm">Role: {{ $record->user->role ?? 'N/A' }}</span>
                    </div>
                </td>
                <td class="p-4 w-1/2 text-center">
                    <div>
                        <img class="h-10 w-14 rounded-full block mx-auto"
                            src="{{ asset($record->engineer->avatar ?? 'uploads/discussion_requests/1737881192.jpg') }}"
                            alt="Avatar">
                        <br>
                        {{ $record->engineer->name ?? 'N/A' }}
                        <br>
                        <span class="text-sm">Email: {{ $record->engineer->email ?? 'N/A' }}</span>
                        <br>
                        <span class="text-sm">Address: {{ $record->engineer->address ?? 'N/A' }}</span>
                        <br>
                        <span class="text-sm">Role: {{ $record->engineer->role ?? 'N/A' }}</span>
                    </div>
                </td>
            </tr>

            <tr class="border">
                <th class="text-left p-4">Type</th>
                <td class="p-4">{{ $record->type ?? 'N/A' }}</td>
            </tr>
            <tr class="border">
                <th class="text-left p-4">Service Title</th>
                <td class="p-4">{{ $record->service_title ?? 'N/A' }}</td>
            </tr>
            <tr class="border">
                <th class="text-left p-4">Description</th>
                <td class="p-4">{{ $record->description ?? 'N/A' }}</td>
            </tr>
            @if ($discussionRequest)
            <tr class="border">
                <th class="text-left p-4">Discussion Request Description</th>
                <td class="p-4">{{ $discussionRequest->description ?? 'N/A' }}</td>
            </tr>
                <tr class="border">
                    <th class="text-left p-4">Price</th>
                    <td class="p-4">{{ $discussionRequest->price ?? 'N/A' }}</td>
                </tr>
                <tr class="border">
                    <th class="text-left p-4">Status</th>
                    <td class="p-4">{{ $discussionRequest->status ?? 'N/A' }}</td>
                </tr>
                <tr class="border">
                    <th class="text-left p-4">Problem Images</th>
                    <td>
                        @if (is_array($discussionRequest->images))
                            @foreach ($discussionRequest->images as $image)
                                <img class="h-10 w-14 rounded-full block mx-auto"
                                src="{{ asset($image) }}"
                                alt="Images">
                            @endforeach
                        @else
                            <img class="h-10 w-14 rounded-full block mx-auto"
                            src="{{ asset($discussionRequest->images ?? 'uploads/discussion_requests/1737881192.jpg') }}"
                            alt="Images">
                        @endif
                    </td>
                </tr>
            @else
                <tr>
                    <td colspan="2" class="text-center p-4 text-red-500">
                        No discussion request found for this user.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</x-filament-panels::page>