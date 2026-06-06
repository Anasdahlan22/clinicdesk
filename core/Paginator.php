<?php
// core/Paginator.php
declare(strict_types=1);

class Paginator
{
    private int $totalItems;
    private int $perPage;
    private int $currentPage;
    private int $totalPages;
    
    public function __construct(int $totalItems, int $perPage, int $currentPage)
    {
        $this->totalItems = max(0, $totalItems);
        $this->perPage = max(1, $perPage);
        $this->totalPages = max(1, (int)ceil($this->totalItems / $this->perPage));
        $this->currentPage = max(1, min($currentPage, $this->totalPages));
    }
    
    public function offset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }
    
    public function perPage(): int
    {
        return $this->perPage;
    }
    
    public function currentPage(): int
    {
        return $this->currentPage;
    }
    
    public function totalPages(): int
    {
        return $this->totalPages;
    }
    
    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }
    
    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }
    
    public function prevPage(): int
    {
        return $this->currentPage - 1;
    }
    
    public function nextPage(): int
    {
        return $this->currentPage + 1;
    }
}