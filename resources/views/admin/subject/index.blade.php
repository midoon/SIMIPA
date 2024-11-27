<x-layout title="Mapel | Admin">
    <x-navbar-admin>
        {{-- header --}}
        <header class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold">Data Mata Pelajaran</h1>
            <div>
                <button onclick="openCreateSubjectModal()"
                    class="px-2 py-2 bg-simipa-1 text-white rounded-lg hover:bg-simipa-2 w-full">
                    Tambah Data Mapel
                </button>

            </div>

        </header>
        <hr class="my-4">

        {{-- error handling --}}
        @error('subject')
            <div class="flex justify-center my-3">
                <div class="text-red-500 font-bold">{{ $message }}</div>
            </div>
        @enderror

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- table --}}
        <div class="relative overflow-x-auto rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right">
                <thead class="text-xs text-simipa-1 uppercase bg-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Nama
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Kelas
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Deskripsi
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Opsi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjects as $subject)
                        <tr class="bg-simipa-6 border-b">
                            <td class="px-6 py-4 ">
                                {{ $subject->name }}
                            </td>
                            <td class="px-6 py-4 ">
                                {{ $subject->grade->name }}
                            </td>
                            <td class="px-6 py-4 ">
                                {{ Str::limit($subject->description, 5, '...') }}
                            </td>
                            <td class="px-6 py-4 ">
                                <div class="flex gap-3">
                                    <button onclick="" data-target="">
                                        <svg class="w-6 h-6 text-gray-800 hover:text-simipa-2 mx-1" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                        </svg>
                                    </button>
                                    <form action="/admin/subject/{{ $subject->id }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirmDeletion()">
                                            <svg class="w-6 h-6 text-grey-800 hover:text-red-700" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-navbar-admin>

    <x-modal-create-subject :grades="$grades"></x-modal-create-subject>

    <script>
        function confirmDeletion() {
            return confirm("Apakah Anda yakin ingin menghapusnya?");
        }

        function openCreateSubjectModal() {
            document.getElementById('createModalSubject').classList.remove('hidden');
        }

        function closeCreateSubjectModal() {
            document.getElementById('createModalSubject').classList.add('hidden');
        }

        // edit
        function openEditSubjectModal(id) {
            document.getElementById('editModalSubject' + id).classList.remove('hidden');
        }

        function closeEditSubjectModal(id) {
            document.getElementById('editModalSubject' + id).classList.add('hidden');
        }
    </script>

</x-layout>
