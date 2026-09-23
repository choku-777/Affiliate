{{-- Laravel標準のBootstrap 5用ページ送りを日本語表記にしたもの（「30件中 1〜20件を表示」の語順にするため上書き） --}}
@if ($paginator->hasPages())
    <nav class="d-flex justify-items-center justify-content-between">
        <div class="d-flex justify-content-between flex-fill d-sm-none">
            <ul class="pagination">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">&lsaquo; 前へ</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo; 前へ</a></li>
                @endif

                @if ($paginator->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">次へ &rsaquo;</a></li>
                @else
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">次へ &rsaquo;</span></li>
                @endif
            </ul>
        </div>

        <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between">
            <div class="small text-muted">
                <span class="fw-semibold">{{ $paginator->total() }}</span>件中
                <span class="fw-semibold">{{ $paginator->firstItem() }}</span>〜<span class="fw-semibold">{{ $paginator->lastItem() }}</span>件を表示
            </div>

            <div>
                <ul class="pagination">
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="前へ"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="前へ">&lsaquo;</a></li>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="次へ">&rsaquo;</a></li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="次へ"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
