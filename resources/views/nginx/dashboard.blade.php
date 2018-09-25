@extends('layouts.template')

@section('head')
    <meta http-equiv="refresh" content="60">
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
    <!-- Page Header -->
    <div class="bg-image overflow-hidden push" style="background-image: url('{{ url('/') }}/assets/img/photos/photo36@2x.jpg');">
        <div class="bg-black-op">
            <div class="content content-full text-center">
                <h1 class="h1 font-w700 text-white animated fadeInDown push-50-t push-5">Web : Overview</h1>
                <h2 class="h3 font-w600 text-white-op animated fadeInDown">Overview of web access on your web servers.</h2>
            </div>
        </div>
    </div>
    <!-- END Page Header -->

    <!-- Range Selector -->
    <div class="row">
        <div class="col-lg-12 text-right">
            <form action="{{ url('/nginx/') }}" method="get">
                <select name="range" id="range">
                    <option value="15m">15 minutes</option>
                    <option value="30m">30 minutes</option>
                    <option value="1h">1 hours</option>
                    <option value="3h">3 hours</option>
                    <option value="6h">6 hours</option>
                    <option value="12h">12 hours</option>
                    <option value="24h">24 hours</option>
                    <option value="3d">3 Days</option>
                    <option value="5d">5 Days</option>
                    <option value="7d">1 Week</option>
                    <option value="14d">2 Weeks</option>
                    <option value="1m">1 Month</option>
                    <option value="3m">3 Months</option>
                    <option value="6m">6 Months</option>
                    <option value="1y">1 Year</option>
                    <option value="2y">2 Year</option>
                </select>
                <button type="submit">Go</button>
            </form>
            <br>
        </div>
    </div>
    <!-- End Range Selector -->

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

    <!-- Top Sites -->
    <div class="row">
        <div class="col-lg-8">
            <div class="block block-opt-refresh-icon4">
                <div class="block-header bg-gray-lighter">
                    <ul class="block-options">
                        <li>
                            <button type="button" data-toggle="block-option" data-action="refresh_toggle" data-action-mode="demo"><i class="si si-refresh"></i></button>
                        </li>
                    </ul>
                    <h3 class="block-title">Top Sites</h3>
                </div>
                <div class="block-content">
                    <table class="table table-borderless table-striped table-vcenter">
                        <thead>
                        <tr>
                            <th>Domain Name</th>
                            <th>Request Count</th>
                            <th>Unique IP</th>
                            <th>Most Visited Country</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($topSites as $siteName => $value)
                            <tr>
                                <td class="text-left" style="width: 100px;"><a href="{{ url('nginx/'.$siteName) }}"><strong>{{ $siteName }}</strong></a></td>
                                <td>{{ $value['requestCount'] }}</td>
                                <td>{{ $value['uniqueIP'] }}</td>
                                <td>{{ $value['mostVisitedCountry'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="block block-opt-refresh-icon4">
                <div class="block-header bg-gray-lighter">
                    <ul class="block-options">
                        <li>
                            <button type="button" data-toggle="block-option" data-action="refresh_toggle" data-action-mode="demo"><i class="si si-refresh"></i></button>
                        </li>
                    </ul>
                    <h3 class="block-title">Top Countries</h3>
                </div>
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
                                <td>{{ (new League\ISO3166\ISO3166())->alpha2($country->key)['name'] }}</td>
                                <td>{{ $country->doc_count }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- END Top Sites -->

    <!-- Latest Events -->
    <div class="row">
        <div class="col-lg-12">
            <div class="block block-opt-refresh-icon4">
                <div class="block-header bg-gray-lighter">
                    <div class="block-options">
                        @if($page>1)
                            <a href="{{ url('/nginx/?page='.($page-1)) }}">&laquo; Previous</a>
                             ...
                        @endif
                        <a href="{{ url('/nginx/?page='.($page+1)) }}">Next &raquo;</a>
                    </div>
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
                                <td class="text-left"><a href="{{ url('nginx/'.$event->_source->siteName) }}"><strong>{{ $event->_source->siteName }}</strong></a></td>
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

        jQuery('#range').val("<?php echo $range; ?>");
    </script>
@endsection