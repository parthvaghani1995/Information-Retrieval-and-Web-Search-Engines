import java.io.IOException;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.StringTokenizer;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;

public class InvertedIndexJob {

 public static class ParthMapper
 extends Mapper < LongWritable, Text, Text, Text > {


  private Text word = new Text();
  Text docid = new Text();

  public void map(LongWritable key, Text value, Context context) throws IOException,
  InterruptedException {

   String line = value.toString();
   StringTokenizer tokens = new StringTokenizer(line);
   String docidstr = tokens.nextToken();
   docid = new Text(docidstr);
   while (tokens.hasMoreTokens()) {
    word.set(tokens.nextToken());
    context.write(word, docid);
   }
  }
 }

 public static class ParthReducer
 extends Reducer < Text, Text, Text, Text > {



  public void reduce(Text key, Iterable < Text > values,
   Context context
  ) throws IOException,
  InterruptedException {

   String tkn;
   HashMap < String, Integer > hmap = new HashMap < String, Integer > ();
   int frequency = 0;
   Iterator < Text > itr = values.iterator();

   while (itr.hasNext()) {
    tkn = itr.next().toString();
    if (hmap.containsKey(tkn)) {
     frequency = (hmap.get(tkn));
     frequency += 1;
     hmap.put(tkn, frequency);
    } else {
     hmap.put(tkn, 1);
    }

   }
   StringBuffer strb = new StringBuffer("");
   for (Map.Entry < String, Integer > map: hmap.entrySet()) {
    strb.append(map.getKey() + ":" + map.getValue() + "\t");
   }
   context.write(key, new Text(strb.toString()));
  }
 }

 public static void main(String[] args) throws Exception {
  Configuration conf = new Configuration();
  Job job = Job.getInstance(conf, "inverted index");
  job.setJarByClass(InvertedIndexJob.class);
  job.setMapperClass(ParthMapper.class);
  job.setReducerClass(ParthReducer.class);
  job.setOutputKeyClass(Text.class);
  job.setOutputValueClass(Text.class);
  FileInputFormat.addInputPath(job, new Path(args[0]));
  FileOutputFormat.setOutputPath(job, new Path(args[1]));
  System.exit(job.waitForCompletion(true) ? 0 : 1);
 }
}
