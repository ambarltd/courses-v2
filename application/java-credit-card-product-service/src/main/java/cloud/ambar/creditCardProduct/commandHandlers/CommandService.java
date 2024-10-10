package cloud.ambar.creditCardProduct.commandHandlers;

import cloud.ambar.creditCardProduct.commands.DefineProductCommand;
import cloud.ambar.creditCardProduct.commands.ProductActivatedCommand;
import cloud.ambar.creditCardProduct.commands.ProductDeactivatedCommand;
import com.fasterxml.jackson.core.JsonProcessingException;

public interface CommandService {
    void handle(DefineProductCommand command) throws JsonProcessingException;
    void handle(ProductActivatedCommand command);
    void handle(ProductDeactivatedCommand command);
}
