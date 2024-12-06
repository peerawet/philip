export const CustomPagination = ({ rowsPerPage, rowCount, currentPage, onChangePage, handlePerRowsChange }) => {
    // Calculate total pages
    const totalPages = Math.ceil(rowCount / rowsPerPage);

    // Calculate the range of rows being displayed
    const startRow = (currentPage - 1) * rowsPerPage + 1;
    const endRow = Math.min(currentPage * rowsPerPage, rowCount);

    // Create an array of page numbers to display in pagination
    const pageNumbers = [];
    for (let i = 1; i <= totalPages; i++) {
        pageNumbers.push(i);
    }

    return (
        <div style={{
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            marginTop: '15px',
            fontFamily: 'Arial, sans-serif',
            fontSize: '14px',
            color: '#333',
            padding: '10px',
            backgroundColor: '#f9f9f9',
            borderRadius: '5px',
            boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
        }}>
            {/* First Page Button */}
            <button
                onClick={() => onChangePage(1)}
                disabled={currentPage === 1}
                style={{
                    marginRight: '10px',
                    padding: '6px 12px',
                    backgroundColor: currentPage === 1 ? '#ddd' : '#007bff',
                    color: '#fff',
                    border: 'none',
                    borderRadius: '4px',
                    cursor: currentPage === 1 ? 'not-allowed' : 'pointer',
                }}
            >
                First
            </button>

            {/* Previous Page Button */}
            <button
                onClick={() => onChangePage(currentPage - 1)}
                disabled={currentPage === 1}
                style={{
                    marginRight: '10px',
                    padding: '6px 12px',
                    backgroundColor: currentPage === 1 ? '#ddd' : '#007bff',
                    color: '#fff',
                    border: 'none',
                    borderRadius: '4px',
                    cursor: currentPage === 1 ? 'not-allowed' : 'pointer',
                }}
            >
                Prev
            </button>

            {/* Page Numbers */}
            <div style={{
                marginRight: '10px',
                display: 'flex',
                alignItems: 'center',
            }}>
                {pageNumbers.map((pageNumber) => (
                    <button
                        key={pageNumber}
                        onClick={() => onChangePage(pageNumber)}
                        style={{
                            padding: '6px 12px',
                            margin: '0 3px',
                            backgroundColor: pageNumber === currentPage ? '#007bff' : '#f1f1f1',
                            color: pageNumber === currentPage ? '#fff' : '#007bff',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            cursor: 'pointer',
                        }}
                    >
                        {pageNumber}
                    </button>
                ))}
            </div>

            {/* Next Page Button */}
            <button
                onClick={() => onChangePage(currentPage + 1)}
                disabled={currentPage === totalPages}
                style={{
                    marginRight: '10px',
                    padding: '6px 12px',
                    backgroundColor: currentPage === totalPages ? '#ddd' : '#007bff',
                    color: '#fff',
                    border: 'none',
                    borderRadius: '4px',
                    cursor: currentPage === totalPages ? 'not-allowed' : 'pointer',
                }}
            >
                Next
            </button>

            {/* Last Page Button */}
            <button
                onClick={() => onChangePage(totalPages)}
                disabled={currentPage === totalPages}
                style={{
                    marginRight: '10px',
                    padding: '6px 12px',
                    backgroundColor: currentPage === totalPages ? '#ddd' : '#007bff',
                    color: '#fff',
                    border: 'none',
                    borderRadius: '4px',
                    cursor: currentPage === totalPages ? 'not-allowed' : 'pointer',
                }}
            >
                Last
            </button>

            {/* Rows Per Page Selector */}
            <select
                value={rowsPerPage}
                onChange={(e) => handlePerRowsChange(Number(e.target.value))}
                style={{
                    marginLeft: '10px',
                    padding: '6px 12px',
                    fontSize: '14px',
                    borderRadius: '4px',
                    border: '1px solid #ccc',
                }}
            >
                {[10, 25, 50, 100].map((option) => (
                    <option key={option} value={option}>
                        {option} rows per page
                    </option>
                ))}
            </select>
        </div>
    );
};
