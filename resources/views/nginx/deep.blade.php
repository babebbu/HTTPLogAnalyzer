@extends('layouts.template')

@section('head')
    <meta http-equiv="refresh" content="15">
@endsection

@section('top-menu')
    <li class="js-header-search header-search">
        <form class="form-horizontal" action="{{ url('/nginx/search') }}" method="post">
            {{ csrf_field() }}
            <div class="form-material form-material-primary input-group remove-margin-t remove-margin-b">
                <input class="form-control" type="text" id="base-material-text" name="domain" placeholder="example.com">
                <span class="input-group-addon"><i class="si si-magnifier"></i></span>
            </div>
        </form>
    </li>
@endsection

@section('content')

    <h1 class="text-center" style="margin-bottom: 20px;">{{ $domain }}</h1>

    <!-- Dashboard Charts -->
    <div class="row">
        <div class="col-lg-12">
            <!-- Bars Chart -->
            <div class="block block-rounded">
                <div class="block-header bg-gray-lighter">
                    <ul class="block-options">
                        <li>
                            <button type="button" data-toggle="block-option" data-action="refresh_toggle" data-action-mode="demo"><i class="si si-refresh"></i></button>
                        </li>
                    </ul>
                    <h3 class="block-title">Events (Last 24 hours)</h3>
                </div>
                <div class="block-content block-content-full text-center">
                    <!-- Bars Chart Container -->
                    <canvas class="js-chartjs2-bars" id="eventChart"></canvas>
                </div>
            </div>
            <!-- END Bars Chart -->
        </div>
    </div>
    <!-- END Dashboard Charts -->

    <!-- Featured -->
    <div class="row">
        <div class="col-lg-6">
            <div class="block block-opt-refresh-icon4">
                <div class="block-header bg-gray-lighter"><h3 class="block-title">Most Visited Countries</h3></div>
                <div class="block-content">
                    <table class="table table-borderless table-striped table-vcenter">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($countries as $country)
                            <tr>
                                <td>{{ (new \League\ISO3166\ISO3166)->alpha2($country->key)['name'] }}</td>
                                <td>{{ $country->doc_count }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="block block-opt-refresh-icon4">
                <div class="block-header bg-gray-lighter"><h3 class="block-title">Top Pages</h3></div>
                <div class="block-content">
                    <table class="table table-borderless table-striped table-vcenter">
                        <thead>
                        <tr>
                            <th>URL</th>
                            <th>Count</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i=0; ?>
                        <?php foreach($topPages as $topPage): ?>
                            <tr>
                                <td>{{ $topPage->key }}</td>
                                <td>{{ $topPage->doc_count }}</td>
                            </tr>
                            <?php //if($i==5){ break; }; $i++ ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="block block-opt-refresh-icon4">
                <div class="block-header bg-gray-lighter"><h3 class="block-title">Browser</h3></div>
                <div class="block-content">
                    <table class="table table-borderless table-striped table-vcenter">
                        <thead>
                        <tr>
                            <th>Browser</th>
                            <th>Count</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($browsers as $browser)
                            <tr>
                                <td>{{ $browser->key }}</td>
                                <td>{{ $browser->doc_count }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="block block-opt-refresh-icon4">
                <div class="block-header bg-gray-lighter"><h3 class="block-title">OS</h3></div>
                <div class="block-content">
                    <table class="table table-borderless table-striped table-vcenter">
                        <thead>
                        <tr>
                            <th>OS & Version</th>
                            <th>Count</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($operatingSystems as $os)
                            <tr>
                                <td>{{ $os->key }}</td>
                                <td>{{ $os->doc_count }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Events -->
    <div class="row">
        <div class="col-lg-12">
            <div class="block block-opt-refresh-icon4">
                <div class="block-header bg-gray-lighter">
                    <ul class="block-options">
                        <li>
                            <button type="button" data-toggle="block-option" data-action="refresh_toggle" data-action-mode="demo"><i class="si si-refresh"></i></button>
                        </li>
                    </ul>
                    <h3 class="block-title">Latest Events</h3>
                </div>
                <div class="block-content">
                    <table class="table table-borderless table-striped table-vcenter">
                        <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Domain Name</th>
                            <th>Details</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($events as $id => $event)
                            <tr>
                                <td style="width: 200px;">{{ $event->_source->read_timestamp }}</td>
                                <td class="text-left"><a href="base_pages_ecom_order.html"><strong>{{ $event->_source->siteName }}</strong></a></td>
                                <td class="hidden-xs">
                                    <dl>
                                        <dt>Source IP</dt>
                                        <dd>{{ $event->_source->nginx->access->remote_ip }}</dd>

                                        <dt>Response Code</dt>
                                        <dd>{{ $event->_source->nginx->access->response_code }}</dd>

                                        <dt>Bytes Sent</dt>
                                        <dd>{{ $event->_source->nginx->access->body_sent->bytes }}</dd>

                                        <dt>Request Method</dt>
                                        <dd>{{ $event->_source->nginx->access->method }}</dd>

                                        <dt>Request URI</dt>
                                        <dd>{{ $event->_source->nginx->access->url }}</dd>

                                        <dt>Referrer</dt>
                                        <dd>{{ $event->_source->nginx->access->referrer }}</dd>

                                        <dt>OS</dt>
                                        <dd>{{ $event->_source->nginx->access->user_agent->os }}</dd>

                                        <dt>User-Agent</dt>
                                        <dd>
                                            {{ $event->_source->nginx->access->user_agent->name }}
                                            {{ (isset($event->_source->nginx->access->user_agent->major))? $event->_source->nginx->access->user_agent->major : '' }}
                                        </dd>

                                        <dt>Country</dt>
                                        <dd>{{ (new League\ISO3166\ISO3166())->alpha2($event->_source->nginx->access->geoip->country_iso_code)['name'] }}</dd>
                                    </dl>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- END Latest Events -->

@endsection

@section('script')
    <script>
        /*
 *  Document   : base_comp_chartjs_v2.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Chart.js v2 Page
 */

        var BaseCompChartJSv2 = function() {
            // Chart.js v2 Charts, for more examples you can check out http://www.chartjs.org/docs
            var initChartsChartJSv2 = function () {
                // Set Global Chart.js configuration
                Chart.defaults.global.defaultFontColor              = '#999';
                Chart.defaults.global.defaultFontFamily             = 'Open Sans';
                Chart.defaults.global.defaultFontStyle              = '600';
                Chart.defaults.scale.gridLines.color               = "rgba(0,0,0,.05)";
                Chart.defaults.scale.gridLines.zeroLineColor       = "rgba(0,0,0,.1)";
                Chart.defaults.global.elements.line.borderWidth     = 2;
                Chart.defaults.global.elements.point.radius         = 4;
                Chart.defaults.global.elements.point.hoverRadius    = 6;
                Chart.defaults.global.tooltips.titleFontFamily      = 'Source Sans Pro';
                Chart.defaults.global.tooltips.bodyFontFamily       = 'Open Sans';
                Chart.defaults.global.tooltips.cornerRadius         = 3;
                Chart.defaults.global.legend.labels.boxWidth        = 15;

                // Get Chart Containers
                var $chart2BarsCon   = jQuery('.js-chartjs2-bars');

                // Set Chart and Chart Data variables
                var $chart2Bars;

                var histogramObject = <?php echo json_encode($histogram); ?>;
                var labelArray = [];
                var dateEventCountArray = [];

                for(i=0, j=23; i<24; i++, j--){
                    labelArray[i] = histogramObject[j].key_as_string;
                    dateEventCountArray[i] = histogramObject[j].doc_count;
                    //console.log('labelArray'+i+' = '+histogramObject[i].key_as_string)
                }

                // Lines/Bar/Radar Chart Data
                var $chart2LinesBarsRadarData = {
                    labels: labelArray,
                    datasets: [
                        {
                            label: 'Event Count',
                            fill: true,
                            backgroundColor: 'rgba(171, 227, 125, .3)',
                            borderColor: 'rgba(171, 227, 125, 1)',
                            pointBackgroundColor: 'rgba(171, 227, 125, 1)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgba(171, 227, 125, 1)',
                            data: dateEventCountArray
                        }
                    ]
                };

                // Init Charts
                $chart2Bars  = new Chart($chart2BarsCon, { type: 'bar', data: $chart2LinesBarsRadarData });
            };

            return {
                init: function () {
                    // Init charts
                    initChartsChartJSv2();
                }
            };
        }();

        // Initialize when page loads
        jQuery(function(){ BaseCompChartJSv2.init(); });
        var ctx = document.getElementById("eventChart");
        ctx.height = 100;
    </script>
@endsection