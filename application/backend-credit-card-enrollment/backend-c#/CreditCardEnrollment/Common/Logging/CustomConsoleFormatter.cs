using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Logging.Abstractions;
using Microsoft.Extensions.Logging.Console;

namespace CreditCardEnrollment.Common.Logging;

public class CustomConsoleFormatterOptions : ConsoleFormatterOptions
{
}

public class CustomConsoleFormatter : ConsoleFormatter
{
    private readonly CustomConsoleFormatterOptions _options;

    public CustomConsoleFormatter(CustomConsoleFormatterOptions options) : base("CustomFormatter")
    {
        _options = options;
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
        var timestamp = DateTimeOffset.Now.ToString(_options.TimestampFormat);
        textWriter.WriteLine($"{timestamp} [{threadId}] [{logEntry.LogLevel}] {message}");
    }
}
