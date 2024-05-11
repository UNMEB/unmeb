@extends('errors::minimal')

@section('title', __('Under Maintenance'))
@section('code', '503')
@section('message', __('System upgrade in progress. Please check again later.'))
