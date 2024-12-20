using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Logging.Abstractions;
using Microsoft.Extensions.Logging.Console;

namespace CreditCardEnrollment.Common.Logging;

public class CustomConsoleFormatter : ConsoleFormatter
{
    public CustomConsoleFormatter() : base("CustomFormatter")
    {
    }

    public override void Write<TState>(
        in LogEntry<TState> logEntry,
        IExternalScopeProvider? scopeProvider,
        TextWriter textWriter)
    {
        var message = logEntry.Formatter?.Invoke(logEntry.State, logEntry.Exception);
        if (message == null)
        {
            return;
        }

        var threadId = Thread.CurrentThread.ManagedThreadId;
        var timestamp = DateTimeOffset.Now.ToString("[yyyy-MM-dd HH:mm:ss.fff] ");
        textWriter.WriteLine($"{timestamp} [{threadId}] [{logEntry.LogLevel}] {message}");
    }
}
