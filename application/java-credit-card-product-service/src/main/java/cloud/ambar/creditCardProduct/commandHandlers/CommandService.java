package cloud.ambar.creditCardProduct.commandHandlers;

import cloud.ambar.creditCardProduct.commands.DefineProductCommand;
import cloud.ambar.creditCardProduct.commands.ProductActivatedCommand;
import cloud.ambar.creditCardProduct.commands.ProductDeactivatedCommand;

public interface CommandService {
    void handle(DefineProductCommand command);
    void handle(ProductActivatedCommand command);
    void handle(ProductDeactivatedCommand command);
}
