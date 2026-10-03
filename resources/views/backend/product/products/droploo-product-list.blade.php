@extends('backend.layouts.app')

@section('content')

    @php
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
    @endphp

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="h3">{{translate('Droploo All products')}}</h1>
            </div>
            <div class="col-auto ml-auto">
                <button type="button" class="btn btn-success" id="btn-import-all" data-toggle="modal" data-target="#import-all-modal" @if(count($notAddedIds) == 0) disabled @endif>
                    <i class="las la-cloud-download-alt"></i> {{ translate('Add All Products') }} ({{ count($notAddedIds) }})
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk import confirm + progress modal -->
    <div class="modal fade" id="import-all-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Add All Products') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" id="import-all-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="import-all-confirm">
                        <p>{{ translate('This will import') }} <strong>{{ count($notAddedIds) }}</strong> {{ translate('products that are not yet added, one by one. This may take a while - please keep this tab open.') }}</p>
                    </div>
                    <div id="import-all-progress" style="display:none;">
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-success" id="import-all-progress-bar" role="progressbar" style="width: 0%;">0%</div>
                        </div>
                        <p class="mb-1">
                            {{ translate('Added') }}: <span id="import-all-added">0</span>,
                            {{ translate('Skipped') }}: <span id="import-all-skipped">0</span>,
                            {{ translate('Failed') }}: <span id="import-all-failed">0</span>
                            / <span id="import-all-total">0</span>
                        </p>
                        <div id="import-all-log" style="max-height: 200px; overflow-y: auto; font-size: 12px;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="button" class="btn btn-success" id="import-all-start">{{ translate('Start Import') }}</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- API Connection Information -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('API Connection Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>{{ translate('Username') }}:</strong> {{ get_setting('droploo_username', 'Not configured') }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>{{ translate('App Key') }}:</strong> {{ get_setting('droploo_app_key', 'Not configured') }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>{{ translate('App Secret') }}:</strong> {{ get_setting('droploo_app_secret', 'Not configured') }}</p>
                        </div>
                    </div>
                    @if(!get_setting('droploo_username') || !get_setting('droploo_app_key') || !get_setting('droploo_app_secret'))
                        <div class="alert alert-warning">
                            {{ translate('Please configure your Droploo API credentials in Business Settings') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <br>

    <div class="card">
        <form class="" id="sort_products" action="{{ route('droploo.products.all') }}" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">{{ translate('Droploo All Product') }}</h5>
                </div>
                <div class="col-auto">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Search by product name') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">{{ translate('Search') }}</button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                    <tr>
                        <th width="5%">SL</th>
                        <th width="5%">Image</th>
                        <th width="20%">Name</th>
                        <th width="8%">Whole Sale Price</th>
                        <th width="10%">Status</th>
                        <th width="5%">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($products as $product)
                        @php
                            $added = \App\Models\Product::where('b_product_id', $product['id'])->first();
                        @endphp
                        <tr>
                            <td>{{ $loop->index+1 }}</td>
                            <td>
                                <img src="{{$imagePath.$product['image']}}" height="50" width="50" />
                            </td>
                            <td>
                                {{ $product['name']}}
                            </td>
                            <td>{{ $product['wholesale_price'] }} Tk.</td>
                            <td>
                                @if($added)
                                    <span class="badge badge-inline badge-success">{{ translate('Added') }}</span>
                                @else
                                    <span class="badge badge-inline badge-secondary">{{ translate('Not Added') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($added)
                                    <span class="btn btn-soft-secondary btn-icon btn-circle btn-sm" title="{{ translate('Already Added') }}">
                                        <i class="las la-check"></i>
                                    </span>
                                @else
                                    <a class="btn btn-soft-success btn-icon btn-circle btn-sm"  href="{{route('droploo.products.add', ['id' => $product['id']])}}" title="{{ translate('ADD') }}">
                                        <i class="las la-plus"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                {{ $products->links() }}
            </div>
            </div>
        </form>
    </div>

@endsection


@section('script')
    <script type="text/javascript">

        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

        $(document).ready(function(){
            //$('#container').removeClass('mainnav-lg').addClass('mainnav-sm');
        });

        function update_todays_deal(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('products.todays_deal') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Todays Deal updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function update_published(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('products.published') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Published products updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function update_approved(el){
            if(el.checked){
                var approved = 1;
            }
            else{
                var approved = 0;
            }
            $.post('{{ route('products.approved') }}', {
                _token      :   '{{ csrf_token() }}',
                id          :   el.value,
                approved    :   approved
            }, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Product approval update successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function update_featured(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('products.featured') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Featured products updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function sort_products(el){
            $('#sort_products').submit();
        }

        var importAllQueue = @json($notAddedIds);
        var importAllRunning = false;

        $('#import-all-start').on('click', function () {
            if (importAllRunning) {
                return;
            }
            importAllRunning = true;

            $('#import-all-confirm').hide();
            $('#import-all-progress').show();
            $('#import-all-start').prop('disabled', true);
            $('#import-all-close').prop('disabled', true);
            $('#btn-import-all').prop('disabled', true);

            var total = importAllQueue.length;
            var added = 0, skipped = 0, failed = 0, done = 0;

            $('#import-all-total').text(total);

            function updateProgress() {
                var pct = total > 0 ? Math.round((done / total) * 100) : 100;
                $('#import-all-progress-bar').css('width', pct + '%').text(pct + '%');
                $('#import-all-added').text(added);
                $('#import-all-skipped').text(skipped);
                $('#import-all-failed').text(failed);
            }

            function logLine(message, cls) {
                $('#import-all-log').prepend('<div class="text-' + cls + '">' + message + '</div>');
            }

            function importNext(index) {
                if (index >= importAllQueue.length) {
                    $.post('{{ route("droploo.products.import_finish") }}', {_token: '{{ csrf_token() }}'})
                        .always(function () {
                            logLine('{{ translate("Import finished. Reloading...") }}', 'primary');
                            setTimeout(function () { location.reload(); }, 1500);
                        });
                    return;
                }

                var productId = importAllQueue[index];

                $.ajax({
                    url: '{{ route("droploo.products.import_one", ["id" => "__ID__"]) }}'.replace('__ID__', productId),
                    type: 'POST',
                    data: {_token: '{{ csrf_token() }}'},
                }).done(function (res) {
                    done++;
                    if (res.status === 'added') {
                        added++;
                        logLine('#' + productId + ' - ' + res.message, 'success');
                    } else if (res.status === 'skipped') {
                        skipped++;
                        logLine('#' + productId + ' - ' + res.message, 'secondary');
                    } else {
                        failed++;
                        logLine('#' + productId + ' - ' + res.message, 'danger');
                    }
                }).fail(function () {
                    done++;
                    failed++;
                    logLine('#' + productId + ' - {{ translate("Request failed") }}', 'danger');
                }).always(function () {
                    updateProgress();
                    importNext(index + 1);
                });
            }

            importNext(0);
        });

        function bulk_delete() {
            var data = new FormData($('#sort_products')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-product-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }

    </script>
@endsection