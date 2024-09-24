<div>
    <table class="w-full">
        <thead>
            <tr>
                <td><strong>Descripción</strong></td>
                <td><strong>Cantidad</strong></td>
                <td><strong>Precio</strong></td>
                <td><strong>Total</strong></td>
            </tr>
            <tr>
                <td colspan="4">
                    <hr>
                </td>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr class="w-full">
                    <td>{{ $product['name'] }}</td>
                    <td>{{ $product['quantity'] }}</td>
                    <td class="text-right">{{ number_format($product['price'], 2) }}</td>
                    <td class="text-right">{{ number_format($product['total'], 2) }}</td>
                </tr>
                @if ($loop->last)
                    <tr>
                        <td colspan="4">
                            <hr>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right" colspan="3"><strong>Total: </strong></td>
                        <td class="text-right"><strong>{{ number_format($sale_total, 2) }}</strong></td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="4">No hay productos</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
