package cloud.ambar.creditCardProduct.query;

import cloud.ambar.creditCardProduct.data.mongo.ReadModelRepository;
import cloud.ambar.creditCardProduct.models.projection.ProductListItem;
import lombok.RequiredArgsConstructor;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.ArrayList;
import java.util.List;

@RequiredArgsConstructor
@Service
public class ListProductsQueryHandler {

    @Autowired
    private ReadModelRepository readModelRepository;

    public List<ProductListItem> getAllProductListItems() {
        return readModelRepository.getAllItems();
    }

}
