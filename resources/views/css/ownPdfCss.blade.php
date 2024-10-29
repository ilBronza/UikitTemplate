<style type="text/css">
@font-face {
  font-family: 'Poppins';
  font-style: normal;
  font-weight: 400;
  src: url('{{ storage_path('fonts/installed/Poppins-Regular.ttf') }}');
}

@font-face {
  font-family: 'Poppins';
  font-style: normal;
  font-weight: 600;
  src: url('{{ storage_path('fonts/installed/Poppins-SemiBold.ttf') }}');
}

@font-face {
  font-family: 'Poppins';
  font-style: normal;
  font-weight: 700;
  src: url('{{ storage_path('fonts/installed/Poppins-Bold.ttf') }}');
}

body *
{
	font-family: Poppins;
	line-height: 1em;
	font-size: 12px;
}

table th
{
	font-weight: 600;
}

.uk-h1, .uk-h2, .uk-h3, .uk-h4, .uk-h5, .uk-h6, .uk-heading-2xlarge, .uk-heading-large, .uk-heading-medium, .uk-heading-small, .uk-heading-xlarge, h2, h3, h4, h5, h6 {
	margin: 0;
	font-family: Poppins;
	font-weight: 700;
	color: #333;
	text-transform: inherit;
	letter-spacing: -.05rem;

	display: inline-block;

	font-size: 14px;

	margin-bottom: 0.1em;
	margin-top: 0.3em;
}

h1 {
	font-size: 24px!important;
	line-height: 1em;
}

table {
	max-width: 100%;
	min-width: 100%;
	background-color: transparent;
	border-collapse: collapse;
	border-spacing: 0;
}
.table td, .table th {
	text-align: left;
	border-top: 1px solid rgba(0, 0, 0, .1);
	padding-top: 0px;
	padding-bottom: 0px;
	padding-right: 0px;
	padding-left: 5px;
}
.table th {
	font-weight: 700;
}
.table thead th {
	vertical-align: bottom;
}
.table caption+thead tr:first-child td, .table caption+thead tr:first-child th, .table colgroup+thead tr:first-child td, .table colgroup+thead tr:first-child th, .table thead:first-child tr:first-child td, .table thead:first-child tr:first-child th {
	border-top: 0;
}
.table tbody+tbody {
	border-top: 2px solid rgba(0, 0, 0, .1);
}
.table-condensed td, .table-condensed th {
	padding: 1px 2px;
}
.table-bordered {
	border: 1px solid rgba(0, 0, 0, .1);
	border-collapse: separate;
	border-left: 0;
}
.table-bordered td, .table-bordered th {
	border-left: 1px solid rgba(0, 0, 0, .1);
}
.table-bordered caption+tbody tr:first-child td, .table-bordered caption+tbody tr:first-child th, .table-bordered caption+thead tr:first-child th, .table-bordered colgroup+tbody tr:first-child td, .table-bordered colgroup+tbody tr:first-child th, .table-bordered colgroup+thead tr:first-child th, .table-bordered tbody:first-child tr:first-child td, .table-bordered tbody:first-child tr:first-child th, .table-bordered thead:first-child tr:first-child th {
	border-top: 0;
}


.table tbody tr.success>td {
	background-color: #00ca65;
}
.table tbody tr.error>td {
	background-color: #ff575f;
}
.table tbody tr.warning>td {
	background-color: #ffb515;
}
.table tbody tr.info>td {
	background-color: #e64577;
}


table.table
{
	font-size: 11px;
}




















.uk-width-1-1
{
	width: 100%}
}
.uk-width-1-2
{
	width: 50%
}
.uk-width-1-3
{
	width: calc(100% * 1 / 3.001);
}
.uk-width-2-3
{
	width: calc(100% * 2 / 3.001);
}
.uk-width-1-4
{
	width: 25%
}
.uk-width-3-4
{
	width: 75%
}
.uk-width-1-5
{
	width: 20%
}
.uk-width-2-5
{
	width: 40%
}
.uk-width-3-5
{
	width: 60%
}
.uk-width-4-5
{
	width: 80%
}
.uk-width-1-6
{
	width: calc(100% * 1 / 6.001);
}
.uk-width-5-6
{
	width: calc(100% * 5 / 6.001);
}
.uk-width-small
{
	width: 150px;
}
.uk-width-medium
{
	width: 300px;
}
.uk-width-large
{
	width: 450px;
}
.uk-width-xlarge
{
	width: 600px;
}
.uk-width-2xlarge
{
	width: 750px;
}
.uk-width-auto
{
	width: auto;
}
.uk-width-expand
{
	flex: 1;
	min-width: 1px;
}


@page { margin: 10px; }
body { margin: 10px 10px; }


.uk-card-small > div
{
	padding: 0!important;
}	
</style>