<div>
    {{-- Filters --}}
    <div x-data="{ show: false}">
        <div class="card bg-light">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Filter
                    </div>

                    <div>
                        <button x-on:click="show = !show" class="btn-plain">
                            <span x-show="!show" x-cloak><i class="fas fa-chevron-down"></i></span>
                            <span x-show="show"><i class="fas fa-chevron-up"></i></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body"
            x-cloak x-show="show">
                <div class="form">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <select name="host" id="host" class="form-select"
                            wire:model="selected_host">
                                <option value="">-- Site --</option>
                                @foreach ($options_host as $host)
                                    <option value="{{ $host }}">{{ $host }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Funnel list --}}
    <div class="mt-3">
        @foreach ($funnels as $Funnel)
            <div class="card mb-2">
                <div class="card-body">
                    <div class="row align-items-center g-2">
                        <div class="col-md">
                            <a href="{{ $Funnel->getRoute() }}">
                                <span class="fs-5">{{ $Funnel->name }}</span>
                            </a>
                        </div>

                        <div class="col-md">
                            Site:
                            {{ $Funnel->hostname }}
                        </div>

                        <div x-data="{ show: false}"
                        class="col-md text-md-end">
                            <div>
                                <button x-on:click="show = !show" class="btn btn-light">
                                    Pages
                                    <span x-show="!show" x-cloak><i class="fas fa-chevron-down"></i></span>
                                    <span x-show="show"><i class="fas fa-chevron-up"></i></span>
                                </button>
                            </div>
                            <div x-show="show" x-cloak class="mt-2 ms-2 ms-md-0 text-secondary">
                                {!! implode('<br>', $Funnel->getPages()) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
